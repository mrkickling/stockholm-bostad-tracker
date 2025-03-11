"""Subscribers of stockholm bostad tracker"""
from enum import Enum
import json

import requests

from .filter import SearchFilter, filter_apartments
from .apartments import Apartment

class NotificationFrequency(Enum):
    """How often the user wants to be notified"""
    DAILY = 'daily'
    WEEKLY = 'weekly'


class Subscriber:
    """A subscriber of this service"""
    def __init__(
        self,
        email: str,
        frequency: NotificationFrequency,
        search_filter: SearchFilter,
        url=None,
    ):
        self.email = email
        self.frequency = frequency
        self.filter = search_filter
        self.url = url

    def __hash__(self):
        return hash(self.email)

    def __repr__(self):
        return f"Subscriber({self.email})"

    def matching_apartments(self, all_apartments: list[Apartment]):
        """Return apartments matching users filter"""
        return filter_apartments(all_apartments, self.filter)


def load_subscribers_from_json(
        subscribers_info_list: list[dict]
    ) -> list[Subscriber]:
    """Load subscribers from list of dicts"""
    subscribers = []
    for subscriber_info in subscribers_info_list:
        subscribers.append(
            Subscriber(
                subscriber_info['email'],
                subscriber_info['frequency'],
                SearchFilter.from_dict(
                    subscriber_info['filter'],
                    published_after=subscriber_info.get('latest_notified')
                ),
                subscriber_info.get('url'),
            )
        )
    return subscribers


def load_subscribers_from_url(url: str):
    """Load JSON from URL
    
    URL should return a list in JSON format matching
    the format described in the README.
    """
    res = requests.get(url, timeout=60)
    res.raise_for_status()
    subscribers_info_list = res.json()
    return load_subscribers_from_json(subscribers_info_list)


def load_subscribers_from_file(json_file: str):
    """Load JSON from file
    
    file should contain a list in JSON format matching
    the format described in the README.
    """
    with open(json_file, 'r', encoding='utf-8') as f:
        subscribers_info_list = json.load(f)
        return load_subscribers_from_json(subscribers_info_list)

def confirm_email_sent(url: str, subscriber: Subscriber):
    """Send the email to a url as comfirmation that email was sent"""
    res = requests.post(
        url, data={'subscriber_email': subscriber.email}, timeout=100
    )
    res.raise_for_status()
