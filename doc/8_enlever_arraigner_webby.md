# Enlever l'arraigner webby

On va dans `config/packages/api_plateform.yaml` et on met `show_webby: false`

Dans `config/packages/api_plateform.yaml`

    api_platform:
        show_webby: false
        mapping:
            paths: ['%kernel.project_dir%/src/Entity']
        patch_formats:
            json: ['application/merge-patch+json']
        swagger:
            versions: [3]