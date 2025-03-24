"""Function to scrape Stockholm Bostad apartments"""

from dataclasses import dataclass, asdict
from datetime import date
import requests

@dataclass
class Apartment:
    """Represents an apartment in Bostad Stockholm"""
    internal_id: int
    city_area: str
    address: str
    kommun: str
    floor: int
    num_rooms: int
    size_sqm: int
    rent: int
    latitude: float
    longitude: float
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

    published_date: str
    last_date: str

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
            internal_id=apartment_info.get('LägenhetId'),
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
            latitude = apartment_info.get('KoordinatLatitud'),
            longitude = apartment_info.get('KoordinatLongitud'),
            has_balcony=apartment_info.get('Balkong'),
            has_elevator=apartment_info.get('Hiss'),
            new_production=apartment_info.get('Nyproduktion'),
            youth=apartment_info.get('Ungdom'),
            student=apartment_info.get('Student'),
            senior=apartment_info.get('Senior'),
            short_lease=apartment_info.get('Korttid'),
            regular=apartment_info.get('Vanlig'),
            apartment_type=apartment_info.get('Lagenhetstyp').lower(),
            published_date=apartment_info.get('AnnonseradFran'),
            last_date=apartment_info.get('AnnonseradTill'),
        )
        apartments.append(apartment)

    return apartments


def upload_apartments(url: str, api_key: str, all_apartments: list[Apartment]):
    """Upload apartments to a database"""
    apartments_url = f"{url}/apartments.php?api_key={api_key}"

    # Upload N apartment at a time
    n = 100
    num_failed = 0
    num_total = 0
    for i in range(0, len(all_apartments), n):
        apartments = all_apartments[i:i+n]
        apartments_dict = [asdict(apartment) for apartment in apartments]
        res = requests.post(apartments_url, json=apartments_dict, timeout=60)
        res.raise_for_status()
        for result in res.json().get('results', []):
            num_total += 1
            if result['status'] == 'error':
                num_failed += 1
    num_successful = num_total - num_failed

    print(
        f"Uploaded {num_successful}/{num_total} apartments"
    )
