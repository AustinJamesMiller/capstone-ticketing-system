# Capstone Ticketing System
This is the final year end project for my IT Systems Management and Security Course
The goal was to create a functioning ticketing system website running on Linux that would utilize transformers and vectorization to automatically categorize tickets.

Completed Goals
- Ticketing system allowing conversation between agents and users with private comments for agents use only
- KBA database with the ability to update old articles and attach images
- Separate user and agent backends
- SQL input sanitization and sterilization via PDO
- Utilized python to implement vectorization transformers to automatically categorize tickets based on subject and content (~85% accuracy)
- Utilized pickle and NumPy to store vector arrays for later reference so new tickets could be scored and added individually instead of having to rescore the entire ticket database
