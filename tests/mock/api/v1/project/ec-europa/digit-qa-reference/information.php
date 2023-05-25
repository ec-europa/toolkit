<?php

header('Content-Type: application/json; charset=utf-8');

echo '
[
    {
        "nid": "2075",
        "name": "digit-qa-reference",
        "acceptance": "https://webgate.acceptance.ec.europa.eu/fpfis/qa",
        "critical": "No",
        "introduction": "[DIGIT] Internal website for Quality Assurance endpoints.",
        "core": "10.0.5",
        "core_acc": "10.0.7",
        "profile_acceptance": "minimal",
        "full_name": "ec-europa/digit-qa-reference",
        "screenshot": "/fpfis/qa/sites/default/files/screenshots/digit-qa-reference.png",
        "dg_agency": "DIGIT",
        "type": "Drupal 9",
        "homepage": "https://digit-dqa.fpfis.tech.ec.europa.eu",
        "production_version": "6.1.0",
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
        "apache_version": "2.4.41-4ubuntu3.14",
        "cluster": "FPFIS",
        "housing_type": "Dedicated",
        "gitlab_timeout": "",
        "housing": "DC",
        "php_version": "8.1.16",
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
        ],
        "availability": ""
    }
]
';
