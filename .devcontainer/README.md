# Docker Dev Container for Visual Studio Code

**This repo supports Visual Studio Code's "[Remote - Container](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)" extension.**

If you have VSCode and Docker installed, then you can develop/debug/test/bench over Docker container.

If you have installed "Remote - Container" extension already, then:

1. Click the green "><" mark at left bottom of VSCode.
2. Select "Remote Containers: Reopen in Container".

Docker will start to build the development container with PHP 7.1 which is the minimum supported version of this repo.

Once the container is up, the VSCode will reload with most of the necessary environments.

- Command Line
  - Opening the "terminal" of VSCode you can run commands inside the container.
  - Ex: `$ composer test all`
- VSCode Extensions installed in the container.
  - [devcontainer.json](devcontainer.json)

## References

The below article might help you understand about developing inside a container.

- [Developing inside a Container](https://code.visualstudio.com/docs/remote/containers) | Docs @ Visual Studio Code
