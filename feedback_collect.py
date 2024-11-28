import sys
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from datetime import datetime, timedelta
import re
import os

# Command line arguments (AWB ID, Start Date, End Date)
awb_id = sys.argv[1]
start_date_str = sys.argv[2]
end_date_str = sys.argv[3]

# Convert date strings to datetime objects
try:
    start_date = datetime.strptime(start_date_str, "%Y-%m-%d")
    end_date = datetime.strptime(end_date_str, "%Y-%m-%d")
except ValueError:
    print(f"Error: Invalid date format. Please use YYYY-MM-DD.")
    sys.exit(1)

# Initialize the WebDriver in headless mode (use the appropriate driver for your browser)
options = webdriver.ChromeOptions()
options.add_argument('--headless')  # Run in headless mode for background operation
driver = webdriver.Chrome(options=options)

# Define the URL for the Amazon product page (use dynamic URL from AWB ID if needed)
# In this example, replace it with a dynamic URL if necessary
product_url = f"https://www.amazon.in/dp/{awb_id}"

# Go to the Amazon product page
driver.get(product_url)

# Use JavaScript to select the "Most recent" option from the review sort dropdown
try:
    dropdown = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.ID, "cm-cr-sort-dropdown"))
    )
    driver.execute_script("arguments[0].value='recent';", dropdown)
    driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", dropdown)
except Exception as e:
    print(f"Error selecting 'Most recent' option: {e}")
    driver.quit()
    sys.exit(1)

# Define date format patterns for Amazon review dates
date_patterns = ["%d %B %Y", "%d %b %Y"]  # Full month name and abbreviated month name

def parse_review_date(date_str):
    """Parses review date from different formats."""
    for pattern in date_patterns:
        try:
            return datetime.strptime(date_str, pattern)
        except ValueError:
            continue
    return None

# Extract review elements
try:
    reviews = WebDriverWait(driver, 10).until(
        EC.presence_of_all_elements_located(
            (By.CSS_SELECTOR, "div[data-hook='review']")
        )
    )

    # To store reviews within the date range
    extracted_reviews = []

    def extract_review_details(review):
        """Extract details of each review."""
        try:
            review_date_str = review.find_element(By.CSS_SELECTOR, "span.review-date").text

            # Extract location and date part from review_date_str
            location_match = re.search(r'Reviewed in ([A-Za-z ]+) on (.+)', review_date_str)
            if location_match:
                review_location = location_match.group(1)
                review_date = parse_review_date(location_match.group(2))
            else:
                review_location = "N/A"
                review_date = None

            # Check if the review is within the given date range
            if review_date and start_date <= review_date <= end_date:
                profile_name = (
                    review.find_element(By.CSS_SELECTOR, "span.a-profile-name").text
                    if review.find_elements(By.CSS_SELECTOR, "span.a-profile-name")
                    else "N/A"
                )

                # Extract star rating from class attribute
                try:
                    star_rating_element = review.find_element(
                        By.CSS_SELECTOR, "i.a-icon-star"
                    )
                    star_class = star_rating_element.get_attribute("class")
                    match = re.search(r"a-star-(\d)", star_class)
                    if match:
                        star_rating = match.group(1)  # Extract the numeric value
                    else:
                        star_rating = "N/A"
                except Exception as e:
                    star_rating = "N/A"

                # Extract review title by data-hook attribute
                try:
                    review_title_element = review.find_element(By.CSS_SELECTOR, "[data-hook='review-title']")
                    review_title = review_title_element.text if review_title_element else "N/A"
                except Exception as e:
                    review_title = "N/A"  # If an exception occurs, set as "N/A"

                # Extract review text
                try:
                    review_text = review.find_element(
                        By.CSS_SELECTOR, "span.review-text"
                    ).text
                except Exception as e:
                    review_text = "N/A"  # If no text is found, set as "N/A"

                # Add review details to the list
                extracted_reviews.append({
                    "Profile Name": profile_name,
                    "Star Rating": star_rating,
                    "Review Title": review_title,
                    "Review Date": review_date.strftime("%d %B %Y"),
                    "Review Text": review_text,
                    "Review Location": review_location
                })
        except Exception as e:
            print(f"Error processing review: {e}")
            return None

    # Extract reviews and store those within the date range
    for review in reviews:
        extract_review_details(review)

    # Check if any reviews were found within the date range
    if not extracted_reviews:
        print(f"No reviews found between {start_date_str} and {end_date_str}.")
    else:
        # Output the extracted reviews
        for review in extracted_reviews:
            print(f"Profile Name: {review['Profile Name']}")
            print(f"Star Rating: {review['Star Rating']}")
            print(f"Review Title: {review['Review Title']}")
            print(f"Review Date: {review['Review Date']}")
            print(f"Review Text: {review['Review Text']}")
            print(f"Review Location: {review['Review Location']}")
            print("\n" + "-" * 50 + "\n")

except Exception as e:
    print(f"Error loading reviews: {e}")

# Close the driver
driver.quit()
