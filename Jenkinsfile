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

        withEnv(["BUILD_ID_UNIQUE=${buildName}_${buildId}","WORKSPACE=${env.WORKSPACE}","PATH+SSK=${env.WORKSPACE}/ssk"]) {

            stage('Init') {
                setBuildStatus("Build started.", "PENDING");
                slackSend color: "good", message: "Subsite build ${buildLink} started."
                shellExecute('jenkins', 'phing', "docker-compose-up -D'docker.project.id'='${env.BUILD_ID_UNIQUE}'")
             }

            try {
                stage('Check') {
                    shellExecute('docker', 'phing', 'setup-php-codesniffer')
                    shellExecute('docker', 'phpcs', 'lib/') 
                }


                stage('Build') {
                    shellExecute('docker', 'phing', "build-dev -D'platform.package.reference'='${params.platformPackageReference}' -D'behat.wd_host.url'='http://selenium:4444/wd/hub' -D'behat.browser.name'='chrome'")
                }

                stage('Test') {
                    //shellExecute('docker', 'phing', "install-dev -D'drupal.db.host'='mysql' -D'drupal.db.name'='${env.BUILD_ID_UNIQUE}'")
                    //timeout(time: 2, unit: 'HOURS') {
                    //    shellExecute('docker', 'phing', 'behat')
                    //}
                }

                stage('Package') {
                    shellExecute('docker', 'phing', "build-release -D'project.release.name'='${env.BUILD_ID_UNIQUE}'")
                    setBuildStatus("Build complete.", "SUCCESS");
                    slackSend color: "good", message: "Subsite build ${buildLink} completed."
                }
            } catch(err) {
                setBuildStatus("Build failed.", "FAILURE");
                slackSend color: "danger", message: "Subsite build ${buildLink} failed."
                throw(err)
            } finally {
                shellExecute('jenkins', 'phing', "docker-compose-stop -D'docker.project.id'='${env.BUILD_ID_UNIQUE}'")
                shellExecute('jenkins', 'phing', "docker-compose-down -D'docker.project.id'='${env.BUILD_ID_UNIQUE}'")
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

def shellExecute(String environment, String executable, String command) {

    switch("${environment}") {
        case "jenkins":
            prefix = ""
            break
        case "docker":
            prefix = "./ssk-${env.BUILD_ID_UNIQUE} exec -T --user jenkins web"
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

    sh "${prefix} ${executable} ${command} ${color}"
}

return this;
