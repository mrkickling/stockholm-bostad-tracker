"""Function to scrape Stockholm Bostad apartments"""

from dataclasses import dataclass
from datetime import date
import requests

@dataclass
class Coordinate:
    """Geographical coordinate"""
    lat: float
    lon: float

@dataclass
class Apartment:
    """Represents an apartment in Bostad Stockholm"""
    id: int
    city_area: str
    address: str
    kommun: str
    floor: int
    num_rooms: int
    size_sqm: int
    rent: int
    coordinate: Coordinate
    url: str

    has_balcony: bool
    has_elevator: bool
    new_production: bool
    youth: bool
    student: bool
    senior: bool
    short_lease: bool
    regular: bool
    apartment_type: str

    published_date: date

    def __str__(self):
        """A string representation of an apartment"""
        return (
            f"{self.address.capitalize()} ({self.city_area.capitalize()}): "
            f"{self.size_sqm or '?'}m2, {self.rent or '?'}kr/mån "
            f"{'Ungdom ' if self.youth else ''}"
            f"{'Student ' if self.youth else ''}"
            f"{'Senior ' if self.senior else ''}- "
            f"{self.url}"
        )

def get_apartments() -> list[Apartment]:
    """Return a list of all currently listed apartments in Stockholm Bostad"""
    url = "https://bostad.stockholm.se/AllaAnnonser/"
    response = requests.get(url, timeout=60)
    response.raise_for_status()
    response = response.json()
    apartments = []
    for apartment_info in response:
        apartment = Apartment(
            id=apartment_info.get('LägenhetId'),
            city_area=apartment_info.get('Stadsdel', '').lower(),
            address=apartment_info.get('Gatuadress').lower(),
            kommun=apartment_info.get('Kommun').lower(),
            floor=apartment_info.get('Vaning'),
            num_rooms=apartment_info.get('AntalRum'),
            size_sqm=apartment_info.get('Yta'),
            rent=apartment_info.get('Hyra'),
            url=(
                "https://bostad.stockholm.se" + apartment_info.get('Url')
            ),
            coordinate=Coordinate(
                apartment_info.get('KoordinatLatitud'),
                apartment_info.get('KoordinatLongitud')
            ),
            has_balcony=apartment_info.get('Balkong'),
            has_elevator=apartment_info.get('Hiss'),
            new_production=apartment_info.get('Nyproduktion'),
            youth=apartment_info.get('Ungdom'),
            student=apartment_info.get('Student'),
            senior=apartment_info.get('Senior'),
            short_lease=apartment_info.get('Korttid'),
            regular=apartment_info.get('Vanlig'),
            apartment_type=apartment_info.get('Lagenhetstyp').lower(),
            published_date=(
                date.fromisoformat(apartment_info.get('AnnonseradFran'))
            )
        )
        apartments.append(apartment)
    return apartments
