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
        def buildName = "${env.JOB_NAME}".replaceAll('%2F','_').replaceAll('/','_').replaceAll('-','_').trim()
        def buildLink = "<${env.BUILD_URL}consoleFull|${buildName} #${env.BUILD_NUMBER}>"

        withEnv([
            "BUILD_ID_UNIQUE=${buildName}_${buildId}",
        ]) {

            stage('Init') {
                setBuildStatus("Build started.", "PENDING");
                slackSend color: "good", message: "Subsite build ${buildLink} started."
                sh "./ssk/phing  start-container -logger phing.listener.AnsiColorLogger"
             }

            try {
                stage('Check') {
                    dockerExecute('./ssk/phing', 'setup-php-codesniffer')
                    dockerExecute('./ssk/phpcs', 'lib/') 
                }


                stage('Build') {
                    dockerExecute('./ssk/phing', "build-dev -D'platform.package.reference'='${params.platformPackageReference}' -D'behat.wd_host.url'='http://selenium:4444/wd/hub' -D'behat.browser.name'='chrome'")
                }

                stage('Test') {
                    //dockerExecute('./ssk/phing', "install-dev -D'drupal.db.host'='mysql' -D'drupal.db.name'='${env.BUILD_ID_UNIQUE}'")
                    //timeout(time: 2, unit: 'HOURS') {
                    //    dockerExecute('./ssk/phing', 'behat')
                    //}
                }

                stage('Package') {
                    dockerExecute('./ssk/phing', "build-release -D'project.release.name'='${env.BUILD_ID_UNIQUE}'")
                    setBuildStatus("Build complete.", "SUCCESS");
                    slackSend color: "good", message: "Subsite build ${buildLink} completed."
                }
            } catch(err) {
                setBuildStatus("Build failed.", "FAILURE");
                slackSend color: "danger", message: "Subsite build ${buildLink} failed."
                throw(err)
            } finally {
                //sh "./ssk/phing stop-container -logger phing.listener.AnsiColorLogger"
            }
        }
}

void setBuildStatus(String message, String state) {
    step([
        $class: "GitHubCommitStatusSetter",
//        contextSource: [$class: "ManuallyEnteredCommitContextSource", context: "${env.BUILD_CONTEXT}"],
        errorHandlers: [[$class: "ChangingBuildStatusErrorHandler", result: "UNSTABLE"]],
        statusResultSource: [$class: "ConditionalStatusResultSource", results: [[$class: "AnyBuildResult", message: message, state: state]]]
    ]);
}

def dockerExecute(String executable, String command) {
    switch("${executable}") {
        case "./ssk/phing":
            color = "-logger phing.listener.AnsiColorLogger"
            break
        case "composer":
            color = "--ansi"
            break
        default:
            color = ""
            break
    }
    sh "docker exec -u jenkins ${BUILD_ID_UNIQUE}_php ${executable} ${command} ${color}"
}

return this;
