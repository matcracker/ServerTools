# ServerTools
ServerTools is Pocketmine-MP plugin containing a set of tools that allows you to manage your server directly from the game!

## Features
- File Explorer
- Cloning
- Restart Server

**Menu UI example:**

![Main_Menu](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_Main.png)

## Commands
- **/servertools** (or alias: **/st**) - _The main command of plugin_ (**Permission:** _st.command.servertools_)

## File Explorer
**Permission:** _st.ui.file-explorer_

It allows to explore and edit your server folder and files.

![FileExplorer](https://github.com/matcracker/ServerTools/blob/master/.github/README_IMAGES/Form_FileExplorer.png)

### Reading file example
![ReadFile](https://github.com/matcracker/ImageContainer/blob/master/ServerTools/Form_FE_ReadFile.gif)

### Editing file example
![EditFile](https://github.com/matcracker/ImageContainer/blob/master/ServerTools/Form_FE_WriteFile.gif)

## Cloning
**Permission:** _st.ui.clone_

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

## Restart Server
**NOT WORKING ON WINDOWS OPERATING SYSTEM!**

**Permission:** _st.ui.restart_

It simply restarts your server.
