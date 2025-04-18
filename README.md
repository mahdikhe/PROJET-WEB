CityPulse - Urban Planning and Collaboration Platform Welcome to CityPulse, a comprehensive platform designed to connect urban planners, architects, and citizens to collaborate on innovative projects that shape the future of our cities. This platform provides tools for project management, event organization, community engagement, and more.

🌟 Features

Landing Page Overview of CityPulse: Introduces the platform and its purpose. Core Features: Highlights project management, event organization, and forum discussions. Call to Action: Encourages users to join the community or explore projects.
Project Management Create Projects: Users can create new projects with details like name, location, category, and tags. View Projects: Browse all active projects with filters for categories, status, and more. Project Details: View detailed information about a project, including budget, timeline, and contributors. Support Projects: Users can support projects, and the platform tracks the number of supporters.
Event Management Event Listings: View upcoming events related to urban planning and community engagement. Event Registration: Users can register for events and receive confirmation. Create Events: Organizers can create and manage events with RSVP functionality.
Community Forums Discussions: Engage in professional discussions on urban planning topics. New Discussions: Users can start new discussions and share ideas. Categories: Forums are organized into categories for better navigation.
Dashboard Creative Dashboard: A personalized dashboard for users to manage their projects, events, and settings. Analytics: Visualize project data with charts and graphs. Settings Panel: Customize the dashboard with theme colors, font sizes, and language preferences.
Contribution System Contribute to Projects: Users can contribute to projects by offering skills, resources, or funding. Contribution Success: A confirmation page for successful contributions.
Calendar Integration Project Calendar: Visualize project timelines and milestones on a calendar. Event Calendar: Track upcoming events and deadlines.
Multilingual Support Languages: Supports multiple languages, including English and French. RTL Support: Full right-to-left layout support for languages like Arabic.
PDF Export Export Data: Generate PDF reports for projects, events, or dashboard analytics. 🚀 Getting Started Prerequisites A local server (e.g., XAMPP, WAMP, or MAMP) to run PHP files. A database with the required tables (projects, events, users, etc.). Installation Clone the repository:
image

Navigate to the project directory: image

Set up the database:

Import the provided SQL files (create_projects_table.sql, create_tasks_table.sql) into your database. Update the database connection file (db.php) with your credentials. Start your local server and open the project in your browser: image

📂 Project Structure

website/ ├── assets/ │ ├── favicon1.png │ ├── logo.png ├── dashboard/ │ ├── all_projects.php │ ├── creative_dashboard.php │ ├── dashboard.html │ ├── db_contributors.php │ ├── delete_project.php │ ├── get_contributor_locations.php │ ├── settings.php │ ├── style.css │ ├── view_project.php ├── front/ │ ├── landingPage.html ├── projet/ │ ├── calendar.php │ ├── contribute.html │ ├── contribute.js │ ├── contribution-success.html │ ├── create_projects_table.sql │ ├── create_tasks_table.sql │ ├── db_contributors.php │ ├── create project/ │ │ ├── createProject.html │ │ ├── projects.php │ │ ├── project-details.php │ │ ├── project_success.php │ │ ├── edit-project.php │ │ ├── cart.php ├── style.css ├── script.js ├── cont.html ├── event.html ├── forums.html ├── new-discussion.html ├── rege.html

🛠️ Technologies Used Frontend HTML5, CSS3, JavaScript (ES6+) Chart.js: For data visualization. Leaflet.js: For interactive maps. Font Awesome: For icons. Backend PHP: For server-side logic. MySQL: For database management. Libraries jsPDF: For PDF export. html2canvas: For capturing dashboard visuals
