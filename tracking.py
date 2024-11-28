import mysql.connector
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
import logging
import configparser
import time

# Set up logging
logging.basicConfig(filename='tracking.log', level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')

def initialize_driver():
    chrome_options = Options()
    chrome_options.add_argument('--headless')  # Run browser in headless mode
    driver = webdriver.Chrome(options=chrome_options)
    driver.maximize_window()
    logging.debug('Web driver initialized')
    return driver

def fetch_tracking_details(awbid):
    logging.debug(f'Fetching tracking details for AWB ID: {awbid}')
    driver = initialize_driver()
    driver.get(f"https://www.delhivery.com/track-v2/package/{awbid}")

    try:
        containers = WebDriverWait(driver, 20).until(EC.presence_of_all_elements_located((By.XPATH, "//div[contains(@class, 'flex items-start w-full')]")))
        logging.debug(f'Containers found: {len(containers)}')
        for container in containers:
            if container.find_elements(By.XPATH, ".//div[contains(@class, 'bg-success w-[40px] h-[40px] flex justify-center items-center rounded-full')]"):
                status_text = container.find_element(By.XPATH, ".//div[contains(@class, 'text-[12px] font-semibold')]").text
                logging.debug(f'Status found: {status_text}')
                return status_text
    except Exception as e:
        logging.error(f'Error fetching tracking details: {e}')
        return "Tracking Not Searchable"
    finally:
        driver.quit()
        logging.debug('Web driver quit')

    return "No status"

def update_tracking_status():
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

        # Fetch all tracking IDs
        cursor.execute("SELECT id, tracking_id FROM shipment_tracking_info")
        rows = cursor.fetchall()
        logging.debug(f'Fetched {len(rows)} tracking IDs from the database')

        for row in rows:
            tracking_id = row[1]
            status = fetch_tracking_details(tracking_id)

            # Update the status in the database
            cursor.execute("UPDATE shipment_tracking_info SET status=%s WHERE id=%s", (status, row[0]))
            conn.commit()
            logging.debug(f'Status updated for ID: {row[0]}')
            time.sleep(2)  # Delay to ensure stability

    except mysql.connector.Error as err:
        logging.error(f'Database error: {err}')
    except Exception as e:
        logging.error(f'Error in update_tracking_status: {e}')
    finally:
        if cursor is not None:
            cursor.close()
        if conn is not None:
            conn.close()
        logging.debug('Database connection closed')

if __name__ == "__main__":
    update_tracking_status()
