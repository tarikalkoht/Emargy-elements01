/**
 * Highlight Paint Worklet for CSS Houdini
 * Provides custom highlight shapes for animated text
 * 
 * @since 2.1.0
 */

// Circle highlight shape
registerPaint('circle', class {
    static get inputProperties() {
        return ['--highlight-color', '--highlight-opacity'];
    }

    paint(ctx, size, props) {
        // Get properties or use defaults
        const color = props.get('--highlight-color').toString().trim() || '#ffc107';
        const opacity = props.get('--highlight-opacity').toString().trim() || '0.3';
        
        // Set fill style
        ctx.fillStyle = color;
        ctx.globalAlpha = parseFloat(opacity);
        
        // Create circle shape
        const radius = Math.min(size.width, size.height) / 2;
        ctx.beginPath();
        ctx.arc(size.width / 2, size.height / 2, radius, 0, 2 * Math.PI);
        ctx.fill();
    }
});

// Curly underline highlight shape
registerPaint('curly', class {
    static get inputProperties() {
        return ['--highlight-color', '--highlight-thickness', '--highlight-y-position'];
    }

    paint(ctx, size, props) {
        // Get properties or use defaults
        const color = props.get('--highlight-color').toString().trim() || '#ffc107';
        const thickness = parseFloat(props.get('--highlight-thickness').toString().trim()) || 3;
        const yPosition = parseFloat(props.get('--highlight-y-position').toString().trim()) || 0.9;
        
        // Set stroke style
        ctx.strokeStyle = color;
        ctx.lineWidth = thickness;
        
        // Position
        const y = size.height * yPosition;
        
        // Draw curly line
        ctx.beginPath();
        
        // Start at left side
        ctx.moveTo(0, y);
        
        // Wave pattern with 4 curves
        const segmentWidth = size.width / 8;
        
        // First curve going up
        ctx.bezierCurveTo(
            segmentWidth, y - thickness * 2,
            segmentWidth * 2, y - thickness * 3,
            segmentWidth * 3, y
        );
        
        // Second curve going down
        ctx.bezierCurveTo(
            segmentWidth * 4, y + thickness * 3,
            segmentWidth * 5, y + thickness * 3,
            segmentWidth * 6, y
        );
        
        // Final curve going up and then down
        ctx.bezierCurveTo(
            segmentWidth * 7, y - thickness * 2,
            segmentWidth * 8, y + thickness * 1,
            size.width, y
        );
        
        ctx.stroke();
    }
});

// Marker highlight shape
registerPaint('marker', class {
    static get inputProperties() {
        return ['--highlight-color', '--highlight-opacity', '--highlight-angle'];
    }

    paint(ctx, size, props) {
        // Get properties or use defaults
        const color = props.get('--highlight-color').toString().trim() || '#ffc107';
        const opacity = parseFloat(props.get('--highlight-opacity').toString().trim()) || 0.5;
        const angle = parseFloat(props.get('--highlight-angle').toString().trim()) || -2;
        
        // Set fill style
        ctx.fillStyle = color;
        ctx.globalAlpha = opacity;
        
        // Create marker shape with slight angle
        ctx.save();
        
        // Rotate slightly
        ctx.translate(size.width / 2, size.height / 2);
        ctx.rotate(angle * Math.PI / 180);
        ctx.translate(-size.width / 2, -size.height / 2);
        
        // Draw a rectangle with rounded ends
        const height = size.height * 0.5;
        const y = (size.height - height) / 2;
        
        ctx.beginPath();
        ctx.roundRect(0, y, size.width, height, [2, 2, 2, 2]);
        ctx.fill();
        
        ctx.restore();
    }
});