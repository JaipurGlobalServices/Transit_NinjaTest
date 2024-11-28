import mysql.connector
import logging
import configparser
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

# Set up logging
logging.basicConfig(filename='send_notifications.log', level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')

def send_email(email, order_id, tracking_id, status, purchase_date, seller_name):
    config = configparser.ConfigParser()
    config.read('config.ini')  # Read the config file

    from_email = config['email']['from_email']
    from_password = config['email']['password']
    smtp_server = config['email']['smtp_server']
    smtp_port = config['email']['smtp_port']

    subject = f"Order Update for {order_id}"
    body = f"""
    Hi,

    Your order with ID {order_id} purchased on {purchase_date} is currently at the following status: {status}.

    Tracking ID: {tracking_id}

    Thank you,
    {seller_name}
    """

    msg = MIMEMultipart()
    msg['From'] = from_email
    msg['To'] = email
    msg['Subject'] = subject
    msg.attach(MIMEText(body, 'plain'))

    try:
        server = smtplib.SMTP_SSL(smtp_server, smtp_port)
        server.login(from_email, from_password)
        server.sendmail(from_email, email, msg.as_string())
        server.quit()
        logging.debug(f"Email sent successfully to {email}")
    except Exception as e:
        logging.error(f"Failed to send email: {e}")

def fetch_and_send_notifications():
    config = configparser.ConfigParser()
    config.read('config.ini')  # Read the config file

    conn = None
    cursor = None
    try:
        # Connect to the database using values from config.ini
        conn = mysql.connector.connect(
            host=config['database']['host'],
            user=config['database']['user'],
            password=config['database']['password'],
            database=config['database']['name']
        )
        cursor = conn.cursor()
        logging.debug('Database connection established')

        # Fetch all necessary details
        cursor.execute("SELECT email, order_id, tracking_id, status, purchase_date, seller_name FROM shipment_tracking_info")
        rows = cursor.fetchall()
        logging.debug(f'Fetched {len(rows)} rows from the database')

        for row in rows:
            email = row[0]
            order_id = row[1]
            tracking_id = row[2]
            status = row[3]
            purchase_date = row[4]
            seller_name = row[5]

            # Send email notification
            send_email(email, order_id, tracking_id, status, purchase_date, seller_name)
            logging.debug(f"Sent email to: {email}")

    except mysql.connector.Error as err:
        logging.error(f'Database error: {err}')
    except Exception as e:
        logging.error(f'Error in fetch_and_send_notifications: {e}')
    finally:
        if cursor is not None:
            cursor.close()
        if conn is not None:
            conn.close()
        logging.debug('Database connection closed')

if __name__ == "__main__":
    fetch_and_send_notifications()
