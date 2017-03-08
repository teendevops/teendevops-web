# About
This file contains the project requirements. It describes what the project will entail. It should serve as a useful guide if someone is wondering how they can contribute.

# Description
The intention of teendevops is to create an scalable and open-source platform on which teenagers who share similar interests in information technology can **connect**, **communicate**, and **collaborate** with each other. teendevops will promote an open and well-documented REST API, and API client libraries for popular languages, including (but not limited to) Java, C#, and Python. It will also feature user-friendly clients for Android and Apple devices, a cross-platform desktop client, and a cross-browser responsive Web client.

## Connection
teendevops will connect end-users using user profiles, following, and direct messaging (see Communication). The users' profiles will act as a portfolio of projects (see Collaboration), and will display basic information (not personally identifiable, as per Terms of Service) about the user.

The users' profiles will:
* enable basic features, such as editing their profile
* display the user icon, username, *optionally* a contact email, user's location, public projects, number of CTF tokens, and user rank (user, moderator, developer, administrator, etc)
* enable moderators to ban or edit a user's profile when deemed necessary

## Communication
teendevops will design and implement a channel-based chat service. The service will incorporate the most favored features from other services including Google Hangouts, Discord, Slack, and Telegram.

The chat service will:
* enable basic features such as creating, deleting, editing, and reporting messages
* support private messaging and "protected" channels
* offer an optional profanity filter, as some users may be young
* enable moderators to be able to delete other user's messages if necessary to avert spam and/or offensive content
* rate-limit messages in order to prevent spam
* promote development and use of artificially-intelligent bots to interact with users naturally

## Collaboration
teendevops will encourage collaboration by allowing the creation of profiles for external projects. These profiles will be displayed on the creator's profile, and will contain an icon, a profile description, a list of collaborators, links to external resources optionally including a project website, and possibly an auto-generated link to an unlisted channel on teendevops for talking about the project.

The projects' profiles will:
* enable basic features, such as editing the project profile
* display the data that is listed in the paragraph above
* enable moderators to be able to edit or delete the projects to prevent spam and/or offensive content
* be displayed on the creator's and collaborator's profiles
* potentially support "up-voting" of projects

# Miscellaneous
teendevops will host a variety of other "sub-projects", including a ctf competition for the users. The sub-projects will be announced on the main page.
