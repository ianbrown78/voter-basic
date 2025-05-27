<p align="center">
  <a href="https://vote.ianbrown.id.au" target="_blank">
    <picture>
      <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/ianbrown78/voter-basic/HEAD/.github/vote-basic-logo.jpg">
      <img alt="Voter Basic" width="315" height="315" style="max-width: 100%" src="https://raw.githubusercontent.com/ianbrown78/voter-basic/HEAD/.github/vote-basic-logo.jpg">
    </picture>
  </a>
</p>

## Project Setup

The quickest way to get started is:
1. Copy the contents of the src directory to the root of your webserver
2. Import the MySQL schema from the db/schema.sql file to your MySQL/MariaDB server.
3. Update config/database.php with your connection credentials for your DB.

## Administration

### Login to Admin

Open your browser to https://your-voter-basic-url/admin/login.php  
The default credentials are:
- username: admin
- password: password123

### Voters

#### Create Voters

Once logged into the admin portal:  
1. Select the Manage Users button on the dashboard.
2. Enter your voters into the form.
3. Click the Add User button.

Alternatively, you can always import a CSV of voter information directly into MySQL using your preferred SQL Client.

#### Delete Voters

Once logged into the admin portal:  
1. Select the Manage Users button on the dashboard.
2. Find the voter at the bottom of the page.
3. Click the delete button beside that voter.

### Elections

#### Create Elections

Once logged into the admin portal:  
1. Click the Manage Elections button from the dashboard.
2. Enter your election details.
3. Click the Add Election button.

You can have multiple elections running at the same time.

#### Delete Elections

Once logged into the admin portal:  
1. Click the Manage Elections button from the dashboard.
2. Find the election at the bottom of the page.
3. Click the delete button beside that election.

You can have multiple elections running at the same time.

### Candidates

#### Create Candidates

Once logged into the admin portal:  
1. Click the Manage Candidates button from the dashboard.
2. Select the election this candidate represents.
3. Add the candidate details.
4. Click the Add Candidate button.

#### Delete Candidates

Once logged into the admin portal:  
1. Click the Manage Candidates button from the dashboard.
2. Find the candidate at the bottom of the page.
3. Click the delete button beside that candidate.