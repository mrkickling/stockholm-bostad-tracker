from bostad_scraper.subscribers import load_subscribers_from_file

def test_load_subscribers_from_file():
    subscribers = load_subscribers_from_file(
        "tests/subscribers.json"
    )
    assert len(subscribers) == 1
    assert subscribers[0].email == 'email@example.com'
