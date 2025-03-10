from bostad_scraper.apartments import get_apartments

def test_get_apartments():
    aptmts = get_apartments()
    assert aptmts
