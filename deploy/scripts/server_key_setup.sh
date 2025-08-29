#!/bin/bash

# Script to set up SSH key authentication for siloe server
# This must be run on the server

# Create .ssh directory with proper permissions
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Create authorized_keys file if it doesn't exist
touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Add the key to authorized_keys
echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC/C1atIVqBUyFFVGHYK49fhzqWjK7F9KxlAsZ88EQB8i0KGbuTAQcMnbmP46sTmCuTuaN/P3OYlJgR5bMIArcs+pf8IMFF1/l+Q1+4suwsnxpkLx7u3/iE5+j+bB+PkG+0HyRhOMYhYFfE3MkHpfMNobn3PHnvIQ+gvUl5ZcmTUYK/HS1waVl+Ekoy37l3CwUu6LxMXNMCxeCHflrjQiCdPZ5nF7QCzuATNAD6kyhhKEMH2GXBXCzzSrm1JydgDy/kVlXdEATLYhzBUt1U6nPuVOvlhPeknVKzO7Lo81fQdCHBVTc2RajbXhdMEAFLuDzW/TmMqAXMJ/2D7OJrjFYh8gTc4EpZ3peGyXxF7ah38F41v6rZdvSC567a4kR4TEi0cgjLr4ySyvyeS4XdvWwRIQYt87ioIHKzjdoP7zUiY7IlmyIGXRW2do2ej+CzSf94sBh0p4/PW2SJLPf1/DMJAR6mOhbWcXqgQylXoxxa7E+bmWdM1yBVrFwntScKsIM= robinklaiss@Robins-MacBook-Pro.local" >> ~/.ssh/authorized_keys

echo "SSH key added successfully!"
echo "File permissions:"
ls -la ~/.ssh
