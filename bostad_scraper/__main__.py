"""CLI to run the scraper"""

import argparse
import os

from .apartments import get_apartments, upload_apartments
from .subscribers import (
    load_subscribers_from_url,
    sync_subscriber
)

def run():
    """Run the cli for stockholm-bostad-tracker"""
    parser = argparse.ArgumentParser()

    parser.add_argument('--base-url', type=str)
    parser.add_argument('--api-key', type=str)

    args = parser.parse_args()

    # environment variables overrides cli args
    base_url = os.getenv('BASE_URL') or args.base_url or ''
    api_key = os.getenv('API_KEY') or args.api_key or ''

    subscribers = load_subscribers_from_url(base_url, api_key)
    all_apartments = get_apartments()
    print(f"Found {len(all_apartments)} apartments")
    upload_apartments(base_url, api_key, all_apartments)
    for subscriber in subscribers:
        sync_subscriber(base_url, api_key, subscriber)
