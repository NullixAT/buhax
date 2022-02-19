<?php

// prevent loading directly in the browser without framelix context
if (!defined("FRAMELIX_MODULE")) {
    die();
}
// this config represents the module configuration defaults
// this are settings that are defined by the module developer
// some keys may be editable in the configuration admin interface
// which then will be saved into config-editable.php
?>
<script type="application/json">
    {
        "backendDefaultView" : "Framelix\\Buhax\\View\\Outgoings",
        "modules": [
            "Pdf",
            "PhpSpreadsheet"
        ],
        "userRoles": {
            "outgoing": "__buhax_view_outgoings__",
            "income": "__buhax_view_incomes__",
            "invoice-1": "__buhax_view_invoice_category_1__",
            "invoice-2": "__buhax_view_invoice_category_2__",
            "fixation": "__buhax_view_fixations__",
            "depreciation": "__buhax_view_depreciation__",
            "reports": "__buhax_view_reports__"
        },
        "moneyUnit": "€",
        "backendLogo": "img/logo-backend.svg",
        "backendIcon": "img/logo.svg",
        "compiler": {
            "Buhax": {
                "js": {
                    "buhax": {
                        "files": [
                            {
                                "type": "folder",
                                "path": "js"
                            }
                        ]
                    }
                },
                "scss": {
                    "buhax": {
                        "files": [
                            {
                                "type": "folder",
                                "path": "scss"
                            }
                        ]
                    }
                }
            }
        }
    }
</script>