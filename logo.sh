#!/bin/bash

# Ensure we are in the project directory
cd /var/www/html/asset_tracker

echo "--- 1. Creating Placeholder Logo File ---"
# We'll create a basic placeholder image in the public directory.
# You will need to replace '/var/www/html/asset_tracker/public/company_logo.png'
# with your actual logo file.
LOGO_PATH="public/company_logo.png"

# This command creates a tiny, transparent placeholder image file.
# NOTE: In a real environment, you would use a dedicated command or tool, but here we just create a file.
touch $LOGO_PATH
echo "Placeholder logo created at: $LOGO_PATH"

# Set permissions for the image file
OWNER_USER=$(logname)
sudo chown $OWNER_USER:www-data $LOGO_PATH
sudo chmod 664 $LOGO_PATH


echo "--- 2. Overwriting application-logo.blade.php ---"
# This file defines the logo component. We replace the SVG with an <img> tag.
cat <<'EOF' > resources/views/components/application-logo.blade.php
<img src="{{ asset('company_logo.png') }}" alt="Company Logo" {{ $attributes->merge(['class' => 'h-10 w-auto']) }}>
EOF

echo "--- 3. Clearing View Cache ---"
# Clearing the view cache ensures the application reloads the component definition.
php artisan view:clear

echo "--- Script complete. ---"
echo "To finalize, you must upload your actual logo and overwrite the placeholder file at:"
echo "    /var/www/html/asset_tracker/public/company_logo.png"
