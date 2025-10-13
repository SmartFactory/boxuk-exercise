# Installation

To create a Codespaces instance, click the `Code` dropdown just above the file list in Github, and select the `Codespaces` tab. Then use the options there to create a new Codespaces instance from this repository.

Once the installation has completed successfully, run the following commands in the Terminal (inside the Codespaces UI) to complete the installation:
```
cd drupal
curl -fsSL https://ddev.com/install.sh | bash
ddev start
ddev composer install
ddev drush site:install --account-name=admin --account-pass=[YOUR CHOSEN PASSWORD]
```

This should result in a fully operational, but basic, Drupal installation.

To access the front-end, select the PORTS tab, and find the row which is shown as running on Port 80. The address listed in the `Forwarded Address` column of this row should give you access to the web site and you should be able to log into the admin using the account name and password you specified earlier.
