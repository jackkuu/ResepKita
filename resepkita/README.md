# Resepkita Project

Resepkita is a web application designed to help users find recipes based on selected ingredients. This project allows users to search for recipes by selecting ingredients from checkboxes, making it easy to discover new dishes based on what they have on hand.

## Project Structure

The project is organized into several directories and files:

- **public/**: Contains the publicly accessible files.
  - **index.php**: Main entry point for the web application.
  - **cari.php**: Handles search requests based on selected ingredients.
  - **css/**: Contains styles for the web application.
    - **style.css**: Defines the visual appearance of the pages.
  - **js/**: Contains JavaScript files for client-side functionality.
    - **main.js**: Handles checkbox selections and AJAX requests.

- **src/**: Contains the source code for the application.
  - **db/**: Database connection files.
    - **connection.php**: Establishes a connection to the "resepkita" database.
  - **models/**: Contains model classes.
    - **Recipe.php**: Defines the `Recipe` class representing a recipe entity.
  - **controllers/**: Contains controller logic.
    - **search.php**: Logic for searching recipes based on selected ingredients.
  - **views/**: Contains view files for rendering HTML.
    - **partials/**: Contains reusable partial views.
      - **header.php**: Header section of the web pages.
      - **footer.php**: Footer section of the web pages.
    - **search_results.php**: Displays search results for recipes.

- **sql/**: Contains SQL files for database setup.
  - **resepkita.sql**: SQL commands to create the "resepkita" database and its tables.

- **composer.json**: Configuration file for Composer, listing project dependencies.

- **.htaccess**: Configuration file for web server settings.

## Setup Instructions

1. **Clone the Repository**: Clone this repository to your local machine.
2. **Set Up the Database**: Import the `resepkita.sql` file into your MySQL database to create the necessary tables.
3. **Configure Database Connection**: Update the database connection settings in `src/db/connection.php` with your database credentials.
4. **Run the Application**: Start your local server (e.g., XAMPP) and navigate to `http://localhost/project/tugas entre/public/index.php` to access the application.

## Usage

- Users can select ingredients from the provided checkboxes on the search page.
- After selecting the desired ingredients, users can submit the form to retrieve matching recipes.
- The application will display the search results, allowing users to explore various recipes based on their selected ingredients.

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue for any suggestions or improvements.

## License

This project is open-source and available under the [MIT License](LICENSE).