FROM python:3.11

# Copy files
COPY bostad_scraper /opt/app/bostad_scraper
COPY pyproject.toml /opt/app/pyproject.toml
WORKDIR /opt/app
RUN pip install --upgrade pip
RUN pip install -e .

ENTRYPOINT bostad_tracker