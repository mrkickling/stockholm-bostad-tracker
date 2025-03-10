from bostad_scraper.email_utils import build_email, EmailSender
from bostad_scraper.subscribers import load_subscribers_from_file
from .mocked_apartments import MOCKED_APARTMENTS

def test_build_email():

    subscribers = load_subscribers_from_file('tests/subscribers.json')

    email_content = build_email(
        subscriber=subscribers[0],
        apartments=MOCKED_APARTMENTS,
    )
    with open('test.html', 'w', encoding='utf-8') as f:
        f.write(email_content)


def test_send_email():

    subscribers = load_subscribers_from_file('tests/subscribers.json')
    email_content = build_email(
        subscriber=subscribers[0],
        apartments=MOCKED_APARTMENTS,
    )
