"""CLI to run the scraper"""

import argparse
import os
from typing import Optional

from dotenv import load_dotenv

from .email_utils import EmailSender, build_email
from .apartments import Apartment, get_apartments
from .subscribers import (
    Subscriber,
    load_subscribers_from_url,
    load_subscribers_from_file,
    confirm_email_sent
)

# Use .env file for email settings
load_dotenv()

def write_results_to_file(
        results: dict[Subscriber, list[Apartment]], filename
    ):
    """Write results to outfile"""
    result_str = ""
    for sub, apartments in results.items():
        result_str += f"# Apartments matching filter for {sub.email}: \n"
        for apartment in apartments:
            result_str += (f"\t- {str(apartment)}\n")

    with open(filename, "w", encoding="utf-8") as outfile:
        outfile.write(result_str)


def send_emails_to_subscribers(
        results: dict[Subscriber, list[Apartment]],
        confirm_email_sent_url: Optional[str] = None
    ):
    """Send email to each subscriber with results"""
    mail_sender = EmailSender(
        os.getenv('mail_server'),
        os.getenv('mail_port'),
        os.getenv('mail_auth_email'),
        os.getenv('mail_auth_pass')
    )

    for subscriber, apartments in results.items():
        if len(apartments) == 0:
            print(
                f"Not sending email to {subscriber.email}, no new apartments"
            )
            continue
        content = build_email(subscriber, apartments)
        mail_sender.send_email(subscriber.email, content)
        if confirm_email_sent_url:
            confirm_email_sent(
                confirm_email_sent_url,
                subscriber
            )


def get_subscriber_apartments(
        subscribers: list[Subscriber]
    ) -> dict[Subscriber, list[Apartment]]:
    """Get the results for each subscriber"""
    subscriber_apartments: dict[Subscriber, list[Apartment]] = {}
    all_apartments = get_apartments()
    for subscriber in subscribers:
        matching_apartments = subscriber.matching_apartments(all_apartments)
        subscriber_apartments[subscriber] = matching_apartments

        print(
            len(matching_apartments),
            f"apartments might match subscriber {subscriber.email}"
        )
    return subscriber_apartments


def run():
    """Run the cli for stockholm-bostad-tracker"""
    parser = argparse.ArgumentParser()
    parser.add_argument('--file', '-f', type=str)
    parser.add_argument('--url', '-u', type=str)
    parser.add_argument(
        '--send-emails',
        type=bool,
        action=argparse.BooleanOptionalAction,
        default=False
    )
    args = parser.parse_args()

    if not(args.file or args.url):
        print("You must set either --file or --url")
        parser.print_help()

    assert not (args.file and args.url), (
        "You can not set both --file and --url, pick one."
    )

    subscribers = []
    if args.file:
        subscribers = load_subscribers_from_file(args.file)
    if args.url:
        subscribers = load_subscribers_from_url(args.url)

    subscriber_apartments = get_subscriber_apartments(subscribers)

    write_results_to_file(subscriber_apartments, "results.txt")
    if args.send_emails:
        send_emails_to_subscribers(subscriber_apartments, args.url)
