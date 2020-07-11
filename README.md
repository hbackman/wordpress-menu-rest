# Wordpress Menu Rest
This plugin allows fetching the menu structure as a rest object.

## Development
```
# Create volumes
docker volume create --name=wp-db
docker volume create --name=wp-uploads

# Build and run images
docker-compose up --build

# Set custom permalink structure under Settings > Permalinks
Custom Structure: /%postname%/
```