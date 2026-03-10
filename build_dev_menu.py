import json
import sys

def main():
    try:
        with open('all_routes.json', 'r') as f:
            routes = json.load(f)
    except FileNotFoundError:
        print("all_routes.json not found")
        sys.exit(1)

    # Filter for GET routes that have a name
    get_routes = [r for r in routes if 'GET' in r.get('method', '') and r.get('name')]
    
    # Exclude typical hidden routes
    ignored_prefixes = ['_ignition', 'sanctum', 'livewire']
    filtered_routes = []
    for r in get_routes:
        uri = r.get('uri', '')
        if not any(uri.startswith(p) for p in ignored_prefixes):
            if '{' not in uri: # only keep routes without parameters for easy clicking
                filtered_routes.append(r)

    # Group by the first segment of the URI or Route Name
    groups = {}
    for r in filtered_routes:
        uri = r['uri']
        segments = [s for s in uri.split('/') if s]
        group_name = segments[0].capitalize() if segments else 'Root'
        
        if group_name not in groups:
            groups[group_name] = []
        groups[group_name].append(r)

    # Output to config/menus/developer.php
    php_content = "<?php\n\nreturn [\n"
    for group_name, routes_list in sorted(groups.items()):
        php_content += f"    [\n"
        php_content += f"        'id' => '{group_name.lower()}_group',\n"
        php_content += f"        'label' => ['en' => '{group_name} Routes', 'ar' => 'مسارات {group_name}'],\n"
        php_content += f"        'icon' => 'build',\n"
        php_content += f"        'children' => [\n"
        for idx, r in enumerate(routes_list):
            name = r['name']
            label = name.replace('.', ' ').title()
            php_content += f"             [\n"
            php_content += f"                'id' => '{name.replace('.', '_')}',\n"
            php_content += f"                'label' => ['en' => '{label}', 'ar' => '{label}'],\n"
            php_content += f"                'route' => '{name}',\n"
            php_content += f"                'icon' => 'link',\n"
            php_content += f"             ],\n"
        php_content += f"        ]\n"
        php_content += f"    ],\n"
        
    php_content += "];\n"

    with open('config/menus/developer.php', 'w') as f:
        f.write(php_content)
        
    print("Done generating config/menus/developer.php")

if __name__ == '__main__':
    main()
