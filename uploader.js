const {
    NodeSSH
} = require('node-ssh');
const ssh = new NodeSSH();
const dotenv = require('dotenv');

// Load environment variables from .env.production file
const envFilePath = `.env.production`;
dotenv.config({
    path: envFilePath
});

// Read server details from environment variables
const remoteServer = {
    host: process.env.VPS_HOST,
    username: process.env.VPS_USERNAME,
    password: process.env.SSH_PASSWORD
};

const remoteDir = process.env.REMOTE_DIR; // 'public_html'
const localDistFolder = './dist'; // Local dist directory
const excludedDirs = ['images']; // Add future folders here if needed

async function uploadFiles() {
    try {
        // Connect to the server
        await ssh.connect(remoteServer);
        console.log('Connected to the server');

        // Build the rm command to exclude certain directories and their content
        const prunePattern = excludedDirs.map(dir => `-path ${remoteDir}/${dir} -prune`).join(' -o ');
        const rmCommand = `find ${remoteDir} -mindepth 1 \\( ${prunePattern} \\) -o -exec rm -rf {} +`;

        // Remove all files in the public_html directory, excluding specified directories and their contents
        await ssh.execCommand(rmCommand);
        console.log(`All files, excluding ${excludedDirs.join(', ')}, and their contents were removed from ${remoteDir}`);

        // Upload all files from the dist folder, including hidden files (e.g., .htaccess)
        await ssh.putDirectory(localDistFolder, remoteDir, {
            recursive: true,
            concurrency: 10, // Number of parallel operations
            tick: (localPath, remotePath, error) => {
                if (error) {
                    console.log(`Error copying ${localPath} to ${remotePath}`);
                } else {
                    console.log(`Successfully copied ${localPath} to ${remotePath}`);
                }
            },
            validate: (localPath) => {
                // Return true for all files, including hidden ones
                const baseName = localPath.split('/').pop();
                return baseName !== '' && baseName !== '.' && baseName !== '..';
            }
        });
        console.log(`Files from ${localDistFolder}, including hidden ones, successfully uploaded to ${remoteDir}`);

        // Grant all permissions to the images folder
        const chmodCommand = `chmod -R 777 ${remoteDir}/images`;
        await ssh.execCommand(chmodCommand);
        console.log('All permissions have been granted to the images folder');

    } catch (err) {
        console.error(`Error: ${err.message}`);
    } finally {
        // Close the SSH connection
        ssh.dispose();
    }
}

uploadFiles();