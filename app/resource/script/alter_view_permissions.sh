#!/bin/bash

sudo find ../../view -type d -exec chgrp http {} \;
sudo find ../../view -type d -exec chmod g+w {} \;
