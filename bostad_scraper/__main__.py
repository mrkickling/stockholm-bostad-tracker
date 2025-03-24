"""CLI to run the scraper"""

import argparse

from dotenv import load_dotenv

from .apartments import get_apartments, upload_apartments
from .subscribers import (
    load_subscribers_from_url,
    sync_subscriber
)

# Use .env file for email settings
load_dotenv()

def run():
    """Run the cli for stockholm-bostad-tracker"""
    parser = argparse.ArgumentParser()

    parser.add_argument('base_url', type=str)
    parser.add_argument('api_key', type=str)

    args = parser.parse_args()

    subscribers = load_subscribers_from_url(args.base_url, args.api_key)
    all_apartments = get_apartments()
    print(f"Found {len(all_apartments)} apartments")
    upload_apartments(args.base_url, args.api_key, all_apartments)
    for subscriber in subscribers:
        print(f"Syncing {subscriber}")
        sync_subscriber(args.base_url, args.api_key, subscriber)
