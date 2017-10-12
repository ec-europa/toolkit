def createWorkflow() {

        properties([
            parameters([
                choice(
                    choices: "2.3.48\n2.2.175\n2.1.84",
                    description: 'Select a platform package reference.',
                    name: 'platformPackageReference')
                ]),
            pipelineTriggers([])
        ])

        // Set some variables.
        def buildId = sh(returnStdout: true, script: 'date |  md5sum | head -c 5').trim()
        def buildName = "${env.JOB_NAME}".replaceAll('ec-europa/','').replaceAll('-reference/','').replaceAll('/','_').replaceAll('-','_').trim()
        def buildLink = "<${env.BUILD_URL}consoleFull|${buildName} #${env.BUILD_NUMBER}>"

        withEnv(["COMPOSE_PROJECT_NAME=${buildName}_${buildId}","WORKSPACE=${env.WORKSPACE}","PATH+toolkit=${env.WORKSPACE}/vendor/ec-europa/toolkit/bin"]) {

            stage('Init') {
                setBuildStatus("Build started.", "PENDING");
                slackSend color: "good", message: "Subsite build ${buildLink} started."
                shellExecute('jenkins', 'phing', "docker-compose-up -D'docker.project.id'='${env.COMPOSE_PROJECT_NAME}'")
             }

            try {
                stage('Upgrade') {
                    shellExecute('docker', 'phing', 'toolkit-starterkit-upgrade')
                }
                stage('Check') {
                    shellExecute('docker', 'phing', 'test-run-phpcs')
                }

                stage('Build') {
                    shellExecute('docker', 'phing', 'build-platform build-subsite-dev')
                }

                stage('Test') {
                    shellExecute('docker', 'phing', "install-clean -D'drupal.db.name'='${env.COMPOSE_PROJECT_NAME}'")
                    timeout(time: 2, unit: 'HOURS') {
                        shellExecute('docker', 'phing', 'test-run-behat')
                    }
                }
            } catch(err) {
                setBuildStatus("Build failed.", "FAILURE");
                slackSend color: "danger", message: "Subsite build ${buildLink} failed."
                throw(err)
            } finally {
                shellExecute('jenkins', 'phing', "docker-compose-stop -D'docker.project.id'='${env.COMPOSE_PROJECT_NAME}'")
                shellExecute('jenkins', 'phing', "docker-compose-down -D'docker.project.id'='${env.COMPOSE_PROJECT_NAME}'")
            }
        }
}

void setBuildStatus(String message, String state) {
    step([
        $class: "GitHubCommitStatusSetter",
        contextSource: [$class: "ManuallyEnteredCommitContextSource", context: "${env.BUILD_CONTEXT}"],
        errorHandlers: [[$class: "ChangingBuildStatusErrorHandler", result: "UNSTABLE"]],
        statusResultSource: [$class: "ConditionalStatusResultSource", results: [[$class: "AnyBuildResult", message: message, state: state]]]
    ]);
}

def shellExecute(String environment, String executable, String command) {

    switch("${environment}") {
        case "jenkins":
            prefix = ""
            break
        case "docker":
            prefix = "./docker-${env.COMPOSE_PROJECT_NAME} exec -T --user web web ./toolkit/"
            break
    }

    switch("${executable}") {
        case "phing":
            color = "-logger phing.listener.AnsiColorLogger"
            break
        case "composer":
            color = "--ansi"
            break
        default:
            color = ""
            break
    }

    sh "${prefix}${executable} ${command} ${color}"
}

return this;
