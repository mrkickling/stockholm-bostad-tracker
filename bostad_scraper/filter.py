"""Filter functionality"""

from dataclasses import dataclass, field
from datetime import date
from typing import Optional
from .apartments import Apartment

# Trust me, this number is the highest possible
INFINITE = 1000000000

@dataclass
class SearchFilter:
    """A filter used to find apartments"""
    # Areas
    city_areas: set[str] = field(default_factory=set)
    kommuns: set[str] = field(default_factory=set)

    # Numbers
    max_floor: int = INFINITE
    min_floor: int = -INFINITE
    min_num_rooms: int = 0
    max_num_rooms: int = INFINITE
    min_size_sqm: int = 0
    max_size_sqm: int = INFINITE
    min_rent: int = 0
    max_rent: int = INFINITE

    # Excluding types (default false)
    require_balcony: bool = False
    require_elevator: bool = False
    require_new_production: bool = False

    # Including types
    include_youth: bool = False
    include_student: bool = False
    include_senior: bool = False
    include_short_lease: bool = True
    include_regular: bool = True

    published_after: Optional[date] = None

    @classmethod
    def from_dict(cls, filter_dict: dict, published_after=None):
        """Create a search filter from a dict"""
        return cls(
            # Areas
            city_areas=set(
                ca.lower() for ca in filter_dict.get('city_areas', [])),
            kommuns=set(
                k.lower() for k in filter_dict.get('kommuns', [])),

            # Numbers
            max_floor=filter_dict.get('max_floor', INFINITE),
            min_floor=filter_dict.get('min_floor', -INFINITE),
            min_num_rooms=filter_dict.get('min_num_rooms', 0),
            max_num_rooms=filter_dict.get('max_num_rooms', INFINITE),
            min_size_sqm=filter_dict.get('min_size_sqm', 0),
            max_size_sqm=filter_dict.get('max_size_sqm', INFINITE),
            min_rent=filter_dict.get('min_rent', 0),
            max_rent=filter_dict.get('max_rent', INFINITE),

            # Requirements
            require_balcony=filter_dict.get('require_balcony', False),
            require_elevator=filter_dict.get('require_elevator', False),
            require_new_production=(
                filter_dict.get('require_new_production', False)
            ),

            # Inclusions
            include_youth=filter_dict.get('include_youth', False),
            include_student=filter_dict.get('include_student', False),
            include_senior=filter_dict.get('include_senior', False),
            include_short_lease=filter_dict.get('include_short_lease', True),
            include_regular=filter_dict.get('include_regular', True),

            # Publish time
            published_after = (
                date.fromisoformat(published_after)
                if published_after else None
            )
        )

    def matches(self, apartment: Apartment) -> bool:
        """Evaluate if apartment matches filter"""
        match = True

        # Area filters
        match &= not self.city_areas or apartment.city_area in self.city_areas
        match &= not self.kommuns or apartment.kommun in self.kommuns

        if apartment.floor:
            match &= self.min_floor <= apartment.floor <= self.max_floor

        if apartment.num_rooms:
            match &= (
                self.min_num_rooms <=
                apartment.num_rooms
                <= self.max_num_rooms
            )

        if apartment.size_sqm:
            match &= (
                self.min_size_sqm <= apartment.size_sqm <= self.max_size_sqm
            )

        if apartment.rent:
            match &= self.min_rent <= apartment.rent <= self.max_rent

        # 'requiring' filters
        match &= apartment.has_balcony if self.require_balcony else True
        match &= apartment.has_elevator if self.require_elevator else True
        match &= (
            apartment.new_production if self.require_new_production else True
        )

        # 'including' filters
        match &= self.include_youth if apartment.youth else True
        match &= self.include_student if apartment.student else True
        match &= self.include_senior if apartment.senior else True
        match &= self.include_short_lease if apartment.short_lease else True
        match &= self.include_regular if apartment.regular else True

        # filter older apartments
        match &= (
            self.published_after is None
            or apartment.published_date >= self.published_after
        )
        return match


def filter_apartments(
        apartments: list[Apartment], search_filter: SearchFilter
    ) -> list[Apartment]:
    """Return all apartments that match the given filter"""
    return [
        apartment for apartment in apartments
        if search_filter.matches(apartment)
    ]
