<?php

header('Content-Type: application/json; charset=utf-8');

echo '
[
    {
        "nid": "2075",
        "name": "digit-qa-reference",
        "acceptance": "https://webgate.acceptance.ec.europa.eu/d/DIGIT/digit-qa",
        "critical": "No",
        "introduction": "[DIGIT] Internal website for Quality Assurance endpoints.",
        "core": "9.4.8",
        "core_acc": "9.4.8",
        "profile_acceptance": "minimal",
        "full_name": "ec-europa/digit-qa-reference",
        "screenshot": "/fpfis/qa/sites/default/files/screenshots/digit-qa-reference.png",
        "dg_agency": "DIGIT",
        "type": "Drupal 9",
        "homepage": "https://webgate.ec.europa.eu/fpfis/qa",
        "production_version": "4.5.2",
        "profile": "minimal",
        "status": "Live",
        "repository": "https://github.com/ec-europa/digit-qa-reference",
        "active_branch": "master",
        "ucr": "Off",
        "usp": "Off",
        "deploy_group_acc": "batch-1",
        "deploy_group_prod": "batch-2",
        "deployment_status": "Privileged",
        "ucr_message": "",
        "usp_message": "",
        "ne_deploy": "False",
        "vcs": "GitHub",
        "github_topics": "deploy-branch-master,housing-dc",
        "gitlab_topics": "location:dc",
        "gitlab_variables": "CI_DEPLOY_BRANCH=master,DIFFY_PROJECT_ID=,CI_DEMO_ENV_SCOPE=shared,TOOLKIT_PROJECT_ID=digit-qa,NO_CICD=True",
        "weight": "0",
        "apache_version": "2.4.41-4ubuntu3.12",
        "cluster": "FPFIS",
        "housing_type": "Shared",
        "gitlab_timeout": "",
        "housing": "DC",
        "php_version": "8.1.12",
        "gitlab_id": "1788",
        "shared_with": [
            {
                "tid": "568",
                "team_id": "digit-qa-developers",
                "team_name": "DIGIT QA Developers",
                "team_type": "developers",
                "team_institution": "DIGIT",
                "members": [
                    {
                        "uid": "1",
                        "name": "test",
                        "username": "test",
                        "email": "fake@ec.europa.eu",
                        "first_name": "Test",
                        "last_name": "JOHN",
                        "department": "DIGIT.D.1.009",
                        "label": "Test JOHN"
                    }
                ]
            }
        ]
    }
]
';
