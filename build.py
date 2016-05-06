#!/usr/bin/python3

import os
import subprocess
import sys
import shutil
import urllib.request
import zipfile

SCRIPT_NAME=os.path.basename(__file__)
PLUGIN_NAME="xdevl-contact-form"
PLUGIN_VERSION="1.0"
PACKAGE_NAME="%s-%s.zip"%(PLUGIN_NAME,PLUGIN_VERSION)

working_directory=sys.path[0]

# Add a directory into a zip file with the given name
def zip_dir(z, name, directory):
	exclude=[".git",".gitignore",SCRIPT_NAME,PACKAGE_NAME,"composer.lock","tests","examples"]
	for entry in os.listdir(directory):
		if entry not in exclude:
			real_path=os.path.join(directory,entry)
			archive_path=os.path.join(name,entry)
			if os.path.isdir(real_path):
				zip_dir(z,archive_path,real_path)
			else:
				z.write(real_path,archive_path)

# Create a zip plugin package
with zipfile.ZipFile(os.path.join(working_directory,PACKAGE_NAME),"w") as z:
	zip_dir(z,PLUGIN_NAME,working_directory)
