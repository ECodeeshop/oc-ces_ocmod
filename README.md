## ocmod module

> **Auto Refresh Modification (Install)**

Upon installing an extension containing an ocmod.xml file, the modification cache is automatically refreshed.

> **Auto Refresh Modification (Uninstall)**

Upon uninstalling an extension containing an ocmod.xml file, the modification cache is automatically refreshed.

> **Modification**

Modification generated files will be available in path
```
____ROOT_PATH____/system/storage/ocmod/modification/
```

> **Ocmod Install Files for Developers**

```
____ROOT_PATH____/extension/____MODULE_NAME____/system/install.ocmod.xml
```

You can add multiple ocmod files (with a .ocmod.xml extension) to modify your OpenCart store.
Ocmod files will be searched for in the following directory structure: ```extension folder > module name > system folder > *.ocmod.xml```

## Installation

1. Open `Upload` folder
2. Zip all the files 
3. Name the zip file as `ces_ocmod.ocmod.zip`
4. Upload the Zip file in your opencart store
`Extensions > Installer`
5. Install `Ces OCMOD`
6. Open `Extensions > Extensions > Modules`
Install `Ces OCMOD`
7. Configure the module status or other settings as per needed 

## Bundle

```sh
cd bundle
```

```sh
npm install
```

```sh
rollup -c
```

Replace Bundle generated <code>editor.bundle.js</code> file with [old file](./upload/admin/view/javascript/)
