# Continuous integration
To check the status of the continuous integration of your project, go to [Drone](https://drone.fpfis.eu/ec-europa).

A pipeline - created and maintained by DIGIT - is applied by default. It manages the code review of the code, runs all the business tests on the repository and builds the site artifact for the deployment. Customizing this pipeline, under express approval, is possible by adding a custom .drone.yml file to the project's root folder. For more detailed information on the pipelines you can visit https://webgate.ec.europa.eu/fpfis/wikis/display/MULTISITE/NE+Pipelines .

## Demo mode

The pipeline allows for an ephemeral environment to be spawned for a limited amount of time. This is done by creating a pull request from a branch named **demo** to the **master** branch. After the project is built a tunnel will open that gives you access to the build. The url comes in the format of:

```
URL saved to /test/toolkit4/.frpc
URL parts save to /test/toolkit4/.frpc.env
HTML email saved to /test/toolkit4/.frpc.html
Starting tunnel at https://user:pass@reponame-buildid1-buildid2-demo.ci.fpfis.tech.ec.europa.eu/
2019/08/06 06:20:17 [I] [control.go:276] [b6a48e313c53a474] login to server success, get run id [b6a48e313c53a474]
2019/08/06 06:20:17 [I] [control.go:411] [b6a48e313c53a474] [reponame-buildid1-buildid2--demo] start proxy success
```

You can simply copy paste the tunnel url and visit your site. When appending /shell to your tunnel url you get access to the build through a web shell.

## Deploy mode

The pipeline allows for an automatic deployment to the acceptance environment. This is done by creating a pull request from a branch named **deploy** to the **master** branch. After the pipeline passes all required tests it will automatically merge your pull request into master and trigger the deployment to the acceptance environment.

### Other topics
- [Setting up a project](/docs/setting-up-project.md)
- [Configuring a project](/docs/configuring-project.md)
- [Installing the project](/docs/installing-project.md)
- [Testing the project](/docs/testing-project.md)
- [Using Docker environment](/docs/docker-environment.md)
- [Building assets](/docs/building-assets.md)
- [Available tasks](/docs/available-tasks.md)
- [Project documentation](/docs/project-documentation.md)
- [Changelog](/CHANGELOG.md)
