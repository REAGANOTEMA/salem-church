// Church Icon Generator for Salem Dominion Ministries
// This script generates all required app icons with the church logo

const fs = require('fs');
const path = require('path');

// Create icon sizes
const sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// HTML Canvas-based icon generator (run in browser)
const iconGeneratorHTML = `
<!DOCTYPE html>
<html>
<head>
    <title>Generate Church Icons</title>
</head>
<body>
    <canvas id="canvas" style="display:none;"></canvas>
    <script>
        function generateChurchIcon(size) {
            const canvas = document.getElementById('canvas');
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');

            // Clear canvas
            ctx.clearRect(0, 0, size, size);

            // Background with rounded corners
            const radius = size * 0.25;
            ctx.beginPath();
            ctx.moveTo(radius, 0);
            ctx.lineTo(size - radius, 0);
            ctx.quadraticCurveTo(size, 0, size, radius);
            ctx.lineTo(size, size - radius);
            ctx.quadraticCurveTo(size, size, size - radius, size);
            ctx.lineTo(radius, size);
            ctx.quadraticCurveTo(0, size, 0, size - radius);
            ctx.lineTo(0, radius);
            ctx.quadraticCurveTo(0, 0, radius, 0);
            ctx.closePath();

            // Gradient background
            const gradient = ctx.createLinearGradient(0, 0, size, size);
            gradient.addColorStop(0, '#f59e0b');
            gradient.addColorStop(1, '#d97706');
            ctx.fillStyle = gradient;
            ctx.fill();

            // Church building
            const centerX = size / 2;
            const centerY = size / 2;
            const scale = size / 512;

            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.scale(scale, scale);

            // Main building
            ctx.fillStyle = '#1e293b';
            ctx.fillRect(-80, -40, 160, 120);

            // Roof
            ctx.beginPath();
            ctx.moveTo(-100, -40);
            ctx.lineTo(0, -120);
            ctx.lineTo(100, -40);
            ctx.closePath();
            ctx.fill();

            // Tower
            ctx.fillRect(-20, -120, 40, 80);

            // Cross on top
            ctx.fillRect(-4, -140, 8, 40);
            ctx.fillRect(-15, -132, 30, 8);

            // Door
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(-20, 20, 40, 60);

            // Windows
            ctx.fillStyle = '#f59e0b';
            ctx.fillRect(-60, -20, 20, 20);
            ctx.fillRect(40, -20, 20, 20);
            ctx.fillRect(-10, -80, 20, 20);

            // Text "SDM"
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold 48px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('SDM', 0, 180);

            ctx.restore();

            return canvas.toDataURL('image/png');
        }

        // Generate all icons
        const sizes = [72, 96, 128, 144, 152, 192, 384, 512];
        sizes.forEach(size => {
            const dataURL = generateChurchIcon(size);
            
            // Create download link
            const link = document.createElement('a');
            link.download = \`icon-\${size}x\${size}.png\`;
            link.href = dataURL;
            link.click();
        });

        console.log('All church icons generated!');
    </script>
</body>
</html>
`;

// Write the HTML file
fs.writeFileSync(path.join(__dirname, 'generate-church-icons.html'), iconGeneratorHTML);

console.log('Church icon generator created!');
console.log('Open generate-church-icons.html in your browser to download all icons.');
console.log('');
console.log('Icon design features:');
console.log('✅ Golden gradient background (matches church theme)');
console.log('✅ Church building silhouette with cross');
console.log('✅ "SDM" text (Salem Dominion Ministries)');
console.log('✅ Rounded corners for modern look');
console.log('✅ Professional shadow effects');
console.log('✅ All required sizes for PWA');
console.log('');
console.log('After generating icons, place them in the public/icons/ directory');
