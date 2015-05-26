# jira-slack-php
Jira-Slack integrator on php

# Features

- Send messages to Slack channels when Jira issues is created, updated and deleted
- Send direct messages to Slack users when Jira issue is assigned to user
- Send messages to different channels from projects Jira-projects
- Show issue changelog into message

# Installation

- Clone repo or download files to your server
- Set your host DocumentRoot into www directory
- Create directory for log file (default - logs) and set it write permissions
- Add Slack incoming Hook integration and put URL into JiraSlackIntegration class config (line 14)
- Add Jira hook with your host URL
- Set $projectsToChannels and $jiraUsersToSlack settings in JiraSlackIntegration class
