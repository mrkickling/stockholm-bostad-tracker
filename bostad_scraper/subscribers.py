"""Subscribers of stockholm bostad tracker"""
from enum import Enum
import json

import requests

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
        url,
        latest_notified,
    ):
        self.email = email
        self.frequency = frequency
        self.url = url
        self.latest_notified = latest_notified

    def __hash__(self):
        return hash(self.email)

    def __repr__(self):
        return f"Subscriber({self.email})"


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
                subscriber_info['url'],
                subscriber_info['latest_notified'],
            )
        )
    return subscribers


def load_subscribers_from_url(url: str, api_key: str):
    """Load JSON from URL
    
    URL should return a list in JSON format matching
    the format described in the README.
    """
    subscribers_url = f"{url}/subscribers.php?api_key={api_key}"
    res = requests.get(subscribers_url, timeout=60)
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


def sync_subscriber(url: str, api_key: str, subscriber: Subscriber):
    """Notify subscriber with new apartments if there are new ones"""
    subscribers_url = (
        f"{url}/sync_subscriber.php?api_key={api_key}"
    )
    res = requests.post(
        subscribers_url,
        data={'email': subscriber.email},
        timeout=60
    )
    res.raise_for_status()
    sync_result = res.json()
    print(sync_result.get('message'))
