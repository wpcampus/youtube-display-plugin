# Example Package
This package should get you started creating plugins for wordpress.

## Initialization
To begin development change the following items:
* build/phpdox.xml - update project name
* src/Example.php - rename file, update class name, update namespace
* tests/Bootstrap.php - update namespace
* tests/phpunit.xml - update testsuite name, update testsuite directory
* tests/ExamplePluginTest - rename directory
* tests/ExamplePluginTest/ExampleTest.php - rename file, update class name, update namespace
* build.xml - update project name
* composer.json - update name, description, authors, autoload
* example.php - rename file, update plugin name and associated comment block fields, update object instantiation

Now build to you heart's content!

## Deployment
After you have developed your plugin there are a couple of things you will need to do to prepare for deployment.
First, make sure you have a repository dedicated to your project on the GitLab server. Make sure you have pushed all
of your work up to the repository. Now you will need to create a job on the Jenkins CI server for your project. When
creating the job copy the `php-template` then uncheck `Disable Build`, setup the `Source Code Management` section,
enable `Build Triggers->Trigger builds remotely`, provide an `Authentication Token`,
then add a post-build action `Send build artifacts over SSH`. Select the server you will be deploying to,
set the source files to `**/` and set the remote directory as the plugin's directory name. You can deploy to as many
servers as you wish to setup. If you do not want to deploy the project but do want to build it just leave out the
`Send build artifacts over SSH` post-build action. Before leaving take note of the remote build URL under the
`Authentication Token` field you filled out previously.

Now that the Jenkins job is setup head over to your project's repo. Under `Settings->Web Hooks` put in the remote
build URL you took note of back on the Jenkins job configuration screen substituting `JENKINS_URL` with `http://ci
.truman.edu` and `TOKEN_NAME` with the token you put in the `Authentication Token` field. Click on `Test Hook` to
check it all out. Over on Jenkins you should see your project build and if you configured the deployment section you
will see your project on each of the deployment servers.

## Note
Permissions are pretty important for all of this to work. If your deployment fails chances are it is due to
permissions. Each of the projects I setup required me to create the plugin's folder on the deployment server and
assign the proper ownership and permissions to it. Then when I built the project it deployed with no errors.
```shell
mkdir example-plugin
chown www-data:www-data example-plugin
chmod 775 example-plugin
```