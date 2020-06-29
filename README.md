[![](https://poggit.pmmp.io/shield.state/ServerTools)](https://poggit.pmmp.io/p/ServerTools)
[![](https://poggit.pmmp.io/shield.dl.total/ServerTools)](https://poggit.pmmp.io/p/ServerTools)
[![Discord](https://img.shields.io/discord/620519017148579841.svg?label=&logo=discord&logoColor=ffffff&color=7389D8&labelColor=6A7EC2)](https://discord.gg/eAePyeb)

# ServerTools
ServerTools is PocketMine-MP plugin containing a set of tools that allows you to manage your server directly from the game!

## Features
- File Explorer
- Cloning
- Plugin Manager
- Poggit Plugin Downloader
- Restart Server

**Menu UI example:**

![Main_Menu](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_Main.png)

## Commands
- **/servertools** (or alias: **/st**) - _The main command of plugin_ (**Permission (default OP)** _st.command.servertools_)

## File Explorer
**Permission (default OP):** _st.ui.file-explorer_
**Permission to create/edit files and folders (default false):** _st.ui.file-explorer.write_

It allows to:
- Explore your server files and folder
- Read your files **(max file size is ~10.25 kB)**
- Create, rename and edit files and folders **(requires write permission)**

![FileExplorer](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_FileExplorer.png)

### Reading file example
![ReadFile](https://github.com/matcracker/ImageContainer/blob/master/ServerTools/Form_FE_ReadFile.gif)

### Editing file example
![EditFile](https://github.com/matcracker/ImageContainer/blob/master/ServerTools/Form_FE_WriteFile.gif)

## Cloning
**Permission (default false):** _st.ui.clone_

It allows to clone your server using **FTP** (also available with SSL) or **SFTP** protocols.

### Requirements
- **FTP**: you need to have installed on your server the **_"ftp"_ PHP extension**.
- **SFTP**: you need to have installed on your server the **_"openssl"_ and _"ssh2 (libssh2)"_ PHP extensions**.

### Steps
1. Select the transfer mode.<br/>
![TransferMode](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_Clone_Transfer.png)

2. Compile the information to login to your FTP/SFTP server.<br/>
![FTP_Data](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_Clone_SFTP.png)

3. Choose, if you want, some server folders/files to exclude from your clonation.<br/>
![ExcludeFiles](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_Clone_ExcludeFiles.png)

4. Wait until the process is finished.<br/>
![Progress](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_Clone_Progress.png)

## Plugin Manager
**Permission (default false):** _st.ui.plugin-manager_

![PluginManager](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_PluginManager.png)

It allows to:
- Enable/Disable plugins<br/>
![EnableDisablePlugin](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_PluginManager_EnDisPlugins.png)

- Load a plugin from file (.phar)<br/>
![LoadPharPlugin](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_PluginManager_LoadPlugin.png)

## Poggit Plugin Downloader
**Permission (default false):** _st.ui.poggit-downloader_

It allows to search and download to your server a plugin from poggit website.

![PoggitDownloader](https://github.com/matcracker/ImageContainer/blob/master/ServerTools/Form_PoggitDownloader.gif)

## Restart Server
**Permission (default OP):** _st.ui.restart_

It simply restarts your server.
