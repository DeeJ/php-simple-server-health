# Simple Server Health
Small PHP script to give a brief overview of the server health. Good for use on shared hosts like cPanel where the dashboard CPU & RAM usage is of only your account and not the entire server. This will instead give you info about the server as a whole.

## Setup
* Copy <code>_conf.sample.php</code> to <code>_conf.php</code> & fill in the database details.
* Ideally this should be password protected. Something like HtPasswd should suffice
