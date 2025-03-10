"""Email related utilities"""

from jinja2 import Environment, FileSystemLoader
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

env = Environment(loader=FileSystemLoader('bostad_scraper/email_templates'))
template = env.get_template('apartments_email.jinja.html')


class EmailSender:

    def __init__(self, server, port, auth_email, auth_pass):
        self.server = server
        self.port = port
        self.auth_email = auth_email
        self.auth_pass = auth_pass

    def send_email(self, email_address: str, content: str):
        """Send email to email address"""
        msg = MIMEMultipart()
        msg['From'] = self.auth_email
        msg['To'] = email_address
        msg['Subject'] = "Stockholm bostad tracker - dagens lägenheter"
        msg.attach(MIMEText(content, 'html'))

        print("Sending email to", email_address)

        with smtplib.SMTP(self.server, self.port, timeout=5) as server:
            server.set_debuglevel(1)  # Enable debugging
            server.ehlo()  # Identify with the mail server
            server.starttls()
            server.ehlo()  # Re-identify after encryption
            server.login(self.auth_email, self.auth_pass)
            server.sendmail(self.auth_email, email_address, msg.as_string())

def build_email(subscriber, apartments):
    """Render email for subscriber with apartment list"""
    rendered_email = template.render(
        subscriber=subscriber,
        apartments=apartments
    )
    return rendered_email
