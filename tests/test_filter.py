"""Tests for each filter"""

from bostad_scraper.filter import SearchFilter, filter_apartments
from .mocked_apartments import MOCKED_APARTMENTS

def test_kommun_filter():
    """Make sure kommun filter works"""
    search_filter = SearchFilter(kommuns={'stockholm'})
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert apartment.kommun == 'stockholm'

def test_city_area_filter():
    """Make sure city area filter works"""
    search_filter = SearchFilter(city_areas={'södermalm'})
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert apartment.city_area == 'södermalm'

def test_floor_filter():
    """Make sure floor filter works"""
    search_filter = SearchFilter(min_floor=2, max_floor=4)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert 2 <= apartment.floor <= 4

def test_room_filter():
    """Make sure room filter works"""
    search_filter = SearchFilter(min_num_rooms=2, max_num_rooms=3)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert 2 <= apartment.num_rooms <= 3

def test_size_filter():
    """Make sure size filter works"""
    search_filter = SearchFilter(min_size_sqm=50, max_size_sqm=70)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert 50 <= apartment.size_sqm <= 70

def test_rent_filter():
    """Make sure rent filter works"""
    search_filter = SearchFilter(min_rent=8000, max_rent=12000)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert 8000 <= apartment.rent <= 12000

def test_balcony_filter():
    """Make sure balcony filter works"""
    search_filter = SearchFilter(require_balcony=True)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert apartment.has_balcony

def test_elevator_filter():
    """Make sure elevator filter works"""
    search_filter = SearchFilter(require_elevator=True)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert apartment.has_elevator

def test_new_production_filter():
    """Make sure new production filter works"""
    search_filter = SearchFilter(require_new_production=True)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert apartment.new_production

def test_youth_filter():
    """Make sure youth filter works"""
    search_filter = SearchFilter(include_youth=True)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert len(filtered_apartments) == len(MOCKED_APARTMENTS)

    search_filter = SearchFilter(include_youth=False)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert len(filtered_apartments) == len(MOCKED_APARTMENTS) - 1

def test_short_lease_filter():
    """Make sure short lease filter works"""
    search_filter = SearchFilter(include_short_lease=False)
    filtered_apartments = filter_apartments(MOCKED_APARTMENTS, search_filter)
    assert filtered_apartments
    for apartment in filtered_apartments:
        assert not apartment.short_lease
