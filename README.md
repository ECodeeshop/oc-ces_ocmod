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

![image](https://github.com/user-attachments/assets/e05849cb-9304-483f-a157-532a4fb148b4)
![image](https://github.com/user-attachments/assets/9cf3fcdd-f765-48f0-8e2b-3a00bad7c201)
![image](https://github.com/user-attachments/assets/200c93ea-feab-4ad1-8e56-33aa6e390e58)


## Upload modules ocmod files
> For admin
1. Create a `index.xml` file
2. Zip `index.xml` and name it as `upload.ocmod.xml`
3. Upload Zip File in `Ces OCMOD > Installer`
4. Refresh Modifications in `Ces OCMOD > Modifications`

> For developers
> 
```
/opt/lampp/htdocs/oc/4023/extension/____MODULE_NAME____/system/install.ocmod.xml
```
You can add multiple ocmod files (with a `.ocmod.xml` extension) to modify your OpenCart store.
Ocmod files will be searched for in the following directory structure: `extension folder > module name > system folder > *.ocmod.xml`

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

Replace Bundle generated <code>editor.bundle.js</code> file with [old file](./4.x.x.x/admin/view/javascript/)
