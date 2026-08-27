#!/usr/bin/env python3
"""
Google Maps Lead Scraper — Hermes Agent / Python Playwright

Scrape business leads from Google Maps (search + continuous scroll),
extract place_id, name, category, phone, website, address, lat/lng,
rating, review_count, then UPSERT into Laravel API
(POST /api/leads/upsert, keyed by google_place_id).

Usage:
  python scraper.py --query "Kontraktor Interior di Jakarta Selatan" --limit 100
  python scraper.py --query "Laundry di Bandung" --limit 50 --headful
  python scraper.py --query "Apotek di Bandung" --limit 30 --no-upload (dry run, print only)
"""

import argparse
import json
import random
import re
import sys
import time
import urllib.request

try:
    from playwright.sync_api import sync_playwright
except ImportError:
    sys.exit("Playwright belum terinstall. Jalankan: pip install playwright && python -m playwright install chromium")

# ---------------------------------------------------------------- config
DEFAULT_API_URL = "http://127.0.0.1:8002/api/leads/upsert"
DEFAULT_API_KEY = "gm-leads-scraper-2026"
USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36",
]


def rand_delay(lo=1.5, hi=3.0):
    time.sleep(random.uniform(lo, hi))


def clean_phone(raw):
    """Normalize phone: keep digits, strip spaces/dashes/parens."""
    if not raw:
        return None
    digits = re.sub(r"[^0-9+]", "", raw)
    if not digits:
        return None
    # Google Maps sering kasih format +62 ... atau 0xxx — simpan apa adanya (digit+plus)
    return digits


def clean_url(raw):
    if not raw:
        return None
    url = raw.strip()
    # buang trailing query params yang cuma tracking (utm, gps, etc) tapi simpan path
    return url


# ---------------------------------------------------------------- extractors
def extract_place_id(page):
    """Place ID canonical Google Maps: 0xHEX:0xHEX (di URL detail)."""
    try:
        url = page.url
        # canonical format: !1s0x...:0x... atau ftid=0x...:0x...
        m = re.search(r"([0-9a-fA-F]{6,}:[0-9a-fA-F]{6,})", url)
        if m:
            return m.group(1)
        m = re.search(r"[?&]ftid=([0-9A-Za-z_\-]+)", url)
        if m:
            return m.group(1)
        return None
    except Exception:
        return None


def extract_lead_from_panel(page, keyword, fallback_name=None):
    """Ambil data dari detail panel (side panel setelah klik listing)."""
    lead = {"google_place_id": None, "business_name": None, "category": None,
            "phone_number": None, "website_url": None, "address": None,
            "latitude": None, "longitude": None, "rating": None,
            "review_count": None, "search_keyword": keyword}

    # Name: h1 spesifik Google Maps (bukan h1 sembarang di halaman)
    try:
        h1 = page.locator("h1.DUwDvf, h1.fontHeadlineSmall, div.DUwDvf").first
        if h1.count():
            txt = h1.inner_text().strip()
            if txt and txt.lower() != "hasil":
                lead["business_name"] = txt
    except Exception:
        pass
    if not lead["business_name"] and fallback_name:
        lead["business_name"] = fallback_name

    # Category: button di bawah nama
    try:
        cat = page.locator("button[jsaction*='category'], button[jsaction*='more'][role='button']").first
        if cat.count():
            txt = cat.inner_text().strip()
            if txt and len(txt) < 100:
                lead["category"] = txt
    except Exception:
        pass

    # Data dari attributes (data-item-id) — source of truth di panel detail
    try:
        # phone
        ph = page.locator("[data-item-id^='phone:tel:']").first
        if ph.count():
            raw = ph.get_attribute("data-item-id") or ""
            lead["phone_number"] = clean_phone(raw.replace("phone:tel:", ""))
        # address
        addr = page.locator("[data-item-id='address']").first
        if addr.count():
            lead["address"] = addr.inner_text().strip()
        # website (authority = nama domain, href = URL lengkap)
        web = page.locator("[data-item-id='authority']").first
        if web.count():
            href = web.get_attribute("href")
            if href and href.startswith("http"):
                lead["website_url"] = clean_url(href)
    except Exception:
        pass

    # Rating & review count: div role=img dengan aria-label "X,X (N) ..."
    try:
        aria = page.locator("div[role='img'][aria-label]").first
        if aria.count():
            label = aria.get_attribute("aria-label") or ""
            nums = re.findall(r"([\d,\.]+)\s*\((\d+)\)", label)
            if nums:
                rating_str, reviews = nums[0]
                lead["rating"] = float(rating_str.replace(",", "."))
                lead["review_count"] = int(reviews)
    except Exception:
        pass

    # Lat/lng dari URL (format @lat,lng)
    try:
        m = re.search(r"@(-?[\d.]+),(-?[\d.]+)", page.url)
        if m:
            lead["latitude"] = float(m.group(1))
            lead["longitude"] = float(m.group(2))
        else:
            # dari data coords di href (!3d...!4d...)
            m = re.search(r"!3d(-?[\d.]+)!4d(-?[\d.]+)", page.url)
            if m:
                lead["latitude"] = float(m.group(1))
                lead["longitude"] = float(m.group(2))
    except Exception:
        pass

    return lead


# ---------------------------------------------------------------- scraper
def scrape(query, limit, headful=False):
    results = []
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=not headful)
        ctx = browser.new_context(
            user_agent=random.choice(USER_AGENTS),
            viewport={"width": 1440, "height": 900},
            locale="id-ID",
            timezone_id="Asia/Jakarta",
        )
        page = ctx.new_page()
        page.add_init_script("""
            Object.defineProperty(navigator, 'webdriver', {get: () => undefined});
            window.chrome = { runtime: {} };
        """)

        url = f"https://www.google.com/maps/search/{urllib.request.quote(query)}/"
        print(f"[*] Buka: {url}")
        page.goto(url, timeout=60000, wait_until="domcontentloaded")
        rand_delay(3, 5)

        # Scroll feed sampai limit tercapai
        stall = 0
        seen_ids = set()
        card_names = []  # (name, index) dari feed — pake index utk stabilitas

        # Fase 1: kumpulin nama + href dari feed (scroll sampai limit / habis)
        print("[*] Fase 1: scan feed...")
        last_total = 0
        while len(card_names) < limit and stall < 8:
            cards = page.locator("div.Nv2PK")
            count = cards.count()
            for i in range(count):
                try:
                    card = cards.nth(i)
                    a = card.locator("a").first
                    href = a.get_attribute("href") if a.count() else None
                    nm = None
                    if a.count():
                        name_el = a.locator("div.fontBodyMedium").first
                        if name_el.count():
                            # nama = baris pertama (fontBodyMedium bisa berisi rating/alamat di baris lain)
                            txt = name_el.inner_text().strip().split("\n")[0]
                            if txt:
                                nm = txt
                    if nm is None:
                        # fallback: dari title attribute link
                        title = a.get_attribute("aria-label") if a.count() else None
                        if title:
                            nm = title.split("·")[0].strip()
                    if nm and href:
                        if (nm, href) not in card_names:
                            card_names.append((nm, href))
                except Exception:
                    continue
            if count == 0:
                stall += 1
            try:
                page.mouse.wheel(0, 2500)
            except Exception:
                pass
            rand_delay(1.5, 3.0)
            # stop kalo total nama gak nambah
            if len(card_names) == last_total:
                stall += 1
            else:
                stall = 0
            last_total = len(card_names)

        print(f"[*] Feed scan: {len(card_names)} nama ditemukan")

        # Fase 2: buka tiap place via href langsung dari card, extract
        print("[*] Fase 2: ekstrak detail per place...")
        for nm, href in card_names:
            if len(results) >= limit:
                break
            try:
                # place_id dari href (!1s0xHEX:0xHEX)
                pid = None
                m = re.search(r"!1s(0x[0-9a-fA-F]+:0x[0-9a-fA-F]+)", href)
                if m:
                    pid = m.group(1)
                else:
                    m = re.search(r"!3s(0x[0-9a-fA-F]+:0x[0-9a-fA-F]+)", href)
                    if m:
                        pid = m.group(1)
                if pid is None:
                    print(f"  [!] {nm}: place_id tidak ketemu di href")
                    continue

                page.goto(href, timeout=45000, wait_until="domcontentloaded")
                page.wait_for_timeout(2500)
                lead = extract_lead_from_panel(page, query, fallback_name=nm)
                lead["google_place_id"] = pid
                if not lead["business_name"]:
                    print(f"  [!] {nm}: business_name kosong")
                    continue
                if lead["google_place_id"] in seen_ids:
                    continue
                
                # SKIP jika sudah punya website
                if lead.get("website_url") and lead["website_url"].strip():
                    print(f"  [!] Skip: {lead['business_name']} (Sudah punya website: {lead['website_url']})")
                    continue
                
                seen_ids.add(lead["google_place_id"])
                results.append(lead)
                print(f"  [{len(results)}/{limit}] {lead['business_name']} | {lead.get('phone_number')} | rating {lead.get('rating')} | {lead['google_place_id']}")
            except Exception as e:
                print(f"  [!] skip: {nm} — {type(e).__name__}: {e}")
                continue

        browser.close()
    return results


# ---------------------------------------------------------------- upload
def upload(results, api_url, api_key, dry_run=False):
    if dry_run:
        print(f"[dry-run] {len(results)} lead (tidak diupload).")
        return len(results), 0

    # batch per 20
    inserted = updated = 0
    for i in range(0, len(results), 20):
        batch = results[i:i + 20]
        payload = json.dumps({"leads": batch}).encode()
        req = urllib.request.Request(api_url, data=payload, method="POST",
                                     headers={"Content-Type": "application/json", "X-Api-Key": api_key})
        try:
            with urllib.request.urlopen(req, timeout=30) as r:
                resp = json.loads(r.read())
            inserted += resp.get("inserted", 0)
            updated += resp.get("updated", 0)
            errs = resp.get("errors", [])
            if errs:
                print(f"  [!] {len(errs)} error di batch {i//20 + 1}: {errs[:3]}")
            print(f"  [upload] batch {i//20 + 1}: +{resp.get('inserted',0)} insert, ~{resp.get('updated',0)} update")
        except Exception as e:
            print(f"  [x] Gagal upload batch {i//20 + 1}: {e}")
    return inserted, updated


# ---------------------------------------------------------------- main
def main():
    ap = argparse.ArgumentParser(description="Google Maps Lead Scraper")
    ap.add_argument("--query", required=True, help='Query pencarian, contoh: "Laundry di Bandung"')
    ap.add_argument("--limit", type=int, default=50, help="Max hasil (default 50)")
    ap.add_argument("--headful", action="store_true", help="Tampilkan browser (debug)")
    ap.add_argument("--api-url", default=DEFAULT_API_URL)
    ap.add_argument("--api-key", default=DEFAULT_API_KEY)
    ap.add_argument("--no-upload", action="store_true", help="Dry run: cetak data tanpa upload")
    args = ap.parse_args()

    print(f"[*] Scrape: '{args.query}' (limit {args.limit})")
    t0 = time.time()
    leads = scrape(args.query, args.limit, args.headful)
    print(f"[*] Ditemukan {len(leads)} lead dalam {time.time()-t0:.1f}s")

    if leads:
        sample = leads[0]
        print(f"  Sample: {json.dumps(sample, ensure_ascii=False, indent=2)}")

    ins, upd = upload(leads, args.api_url, args.api_key, args.no_upload)
    print(f"[*] Selesai. Inserted: {ins} | Updated: {upd}")


if __name__ == "__main__":
    main()
