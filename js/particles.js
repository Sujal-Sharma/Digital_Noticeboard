/**
 * SRMAP Noticeboard — Galaxy Particle Animation
 * Dark space theme: glowing nodes, connection lines, mouse repulsion, click bursts
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('particles-js');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let particlesArray = [];
        let animFrameId = null;

        // ── Mouse state ──────────────────────────────────────────────────────
        const mouse = {
            x: null,
            y: null,
            radius: 140
        };

        // ── Resize handler ───────────────────────────────────────────────────
        function resizeCanvas() {
            const parent = canvas.parentElement;
            canvas.width  = parent ? parent.offsetWidth  : window.innerWidth;
            canvas.height = parent ? parent.offsetHeight : window.innerHeight;
        }

        resizeCanvas();

        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                resizeCanvas();
                initParticles();
            }, 150);
        });

        // ── Mouse / touch tracking ────────────────────────────────────────────
        canvas.addEventListener('mousemove', function (e) {
            const rect = canvas.getBoundingClientRect();
            mouse.x = e.clientX - rect.left;
            mouse.y = e.clientY - rect.top;
        });

        window.addEventListener('mousemove', function (e) {
            const rect = canvas.getBoundingClientRect();
            mouse.x = e.clientX - rect.left;
            mouse.y = e.clientY - rect.top;
        });

        window.addEventListener('mouseout', function () {
            mouse.x = null;
            mouse.y = null;
        });

        // ── Click burst ───────────────────────────────────────────────────────
        window.addEventListener('click', function (e) {
            const rect = canvas.getBoundingClientRect();
            const cx = e.clientX - rect.left;
            const cy = e.clientY - rect.top;
            const burstCount = 8;
            for (let i = 0; i < burstCount; i++) {
                const angle  = (i / burstCount) * Math.PI * 2;
                const speed  = Math.random() * 4 + 2;
                particlesArray.push(new Particle(
                    cx, cy,
                    Math.random() * 2.5 + 1,
                    getRandomGlowColor(),
                    {
                        x: Math.cos(angle) * speed,
                        y: Math.sin(angle) * speed
                    },
                    true // burst flag — short lifetime
                ));
            }
        });

        // ── Color palette ─────────────────────────────────────────────────────
        const colorPalette = [
            // white-ish
            'rgba(255, 255, 255, 0.9)',
            'rgba(220, 235, 255, 0.85)',
            // cyan / light-blue
            'rgba(100, 210, 255, 0.85)',
            'rgba(80,  190, 255, 0.80)',
            'rgba(140, 220, 255, 0.75)',
            // primary blue
            'rgba(79,  127, 255, 0.85)',
            'rgba(100, 149, 255, 0.75)',
            // soft purple accent
            'rgba(160, 140, 255, 0.65)',
        ];

        function getRandomGlowColor() {
            return colorPalette[Math.floor(Math.random() * colorPalette.length)];
        }

        // ── Particle class ────────────────────────────────────────────────────
        class Particle {
            constructor(x, y, size, color, velocity, isBurst) {
                this.x        = x;
                this.y        = y;
                this.size     = size;
                this.baseSize = size;
                this.color    = color;
                this.velocity = velocity || {
                    x: (Math.random() - 0.5) * 0.8,
                    y: (Math.random() - 0.5) * 0.8
                };
                this.isBurst  = isBurst || false;
                this.life     = isBurst ? 1.0 : null; // 0→dead for burst particles
                this.density  = Math.random() * 20 + 5;
                this.twinkle  = Math.random() * Math.PI * 2; // phase offset for pulse
                this.twinkleSpeed = Math.random() * 0.04 + 0.01;
            }

            draw() {
                const alpha = this.isBurst ? this.life : 1;
                const r     = this.size;
                const glowR = r * 4;

                // Outer glow
                const grad = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, glowR);
                const baseColor = this.color.replace(/[\d.]+\)$/, (alpha * 0.35) + ')');
                grad.addColorStop(0,   this.color.replace(/[\d.]+\)$/, (alpha * 0.9) + ')'));
                grad.addColorStop(0.4, this.color.replace(/[\d.]+\)$/, (alpha * 0.4) + ')'));
                grad.addColorStop(1,   this.color.replace(/[\d.]+\)$/, '0)'));

                ctx.beginPath();
                ctx.arc(this.x, this.y, glowR, 0, Math.PI * 2);
                ctx.fillStyle = grad;
                ctx.fill();

                // Core dot
                ctx.beginPath();
                ctx.arc(this.x, this.y, r, 0, Math.PI * 2);
                ctx.fillStyle = this.color.replace(/[\d.]+\)$/, alpha + ')');

                // Glow shadow
                ctx.shadowBlur  = 12;
                ctx.shadowColor = this.color.replace(/[\d.]+\)$/, (alpha * 0.8) + ')');
                ctx.fill();
                ctx.shadowBlur  = 0;
                ctx.shadowColor = 'transparent';
            }

            update() {
                // Twinkle — pulse size slightly
                this.twinkle += this.twinkleSpeed;
                const pulseFactor = 1 + Math.sin(this.twinkle) * 0.15;
                this.size = this.baseSize * pulseFactor;

                // Bounce off edges (with small margin)
                if (this.x < 0 || this.x > canvas.width) {
                    this.velocity.x *= -0.85;
                    this.x = Math.max(0, Math.min(canvas.width, this.x));
                }
                if (this.y < 0 || this.y > canvas.height) {
                    this.velocity.y *= -0.85;
                    this.y = Math.max(0, Math.min(canvas.height, this.y));
                }

                // Subtle random drift
                if (!this.isBurst && Math.random() < 0.025) {
                    this.velocity.x += (Math.random() - 0.5) * 0.15;
                    this.velocity.y += (Math.random() - 0.5) * 0.15;
                }

                // Speed cap for ambient particles
                if (!this.isBurst) {
                    const speed = Math.sqrt(this.velocity.x ** 2 + this.velocity.y ** 2);
                    if (speed > 1.2) {
                        this.velocity.x *= 0.97;
                        this.velocity.y *= 0.97;
                    }
                }

                // Mouse REPULSION
                if (mouse.x !== null && mouse.y !== null) {
                    const dx       = this.x - mouse.x;
                    const dy       = this.y - mouse.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < mouse.radius && distance > 0) {
                        const force     = (mouse.radius - distance) / mouse.radius;
                        const pushX     = (dx / distance) * force * this.density * 0.55;
                        const pushY     = (dy / distance) * force * this.density * 0.55;
                        this.velocity.x += pushX;
                        this.velocity.y += pushY;

                        // Glow brighter on repulsion
                        this.size = Math.min(this.baseSize * 2.5, this.size + force * 1.5);
                    }
                }

                // Apply velocity
                this.x += this.velocity.x;
                this.y += this.velocity.y;

                // Burst particle fade out
                if (this.isBurst) {
                    this.life -= 0.018;
                    this.velocity.x *= 0.96;
                    this.velocity.y *= 0.96;
                }
            }

            isDead() {
                return this.isBurst && this.life <= 0;
            }
        }

        // ── Init particles ────────────────────────────────────────────────────
        function particleCount() {
            const area = canvas.width * canvas.height;
            // Clamp between 60 and 200
            return Math.min(200, Math.max(60, Math.floor(area / 7000)));
        }

        function initParticles() {
            particlesArray = [];
            const count = particleCount();
            for (let i = 0; i < count; i++) {
                particlesArray.push(new Particle(
                    Math.random() * canvas.width,
                    Math.random() * canvas.height,
                    Math.random() * 1.8 + 0.5,
                    getRandomGlowColor(),
                    {
                        x: (Math.random() - 0.5) * 0.7,
                        y: (Math.random() - 0.5) * 0.7
                    }
                ));
            }
        }

        // ── Connection lines ──────────────────────────────────────────────────
        const MAX_DIST = 130;

        function drawConnections() {
            const len = particlesArray.length;
            for (let i = 0; i < len; i++) {
                const p1 = particlesArray[i];
                if (p1.isBurst) continue;

                for (let j = i + 1; j < len; j++) {
                    const p2 = particlesArray[j];
                    if (p2.isBurst) continue;

                    const dx   = p1.x - p2.x;
                    const dy   = p1.y - p2.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < MAX_DIST) {
                        const opacity = (1 - dist / MAX_DIST) * 0.45;

                        // Gradient line for glow feel
                        const grad = ctx.createLinearGradient(p1.x, p1.y, p2.x, p2.y);
                        grad.addColorStop(0, `rgba(100, 180, 255, ${opacity})`);
                        grad.addColorStop(0.5, `rgba(160, 210, 255, ${opacity * 1.3})`);
                        grad.addColorStop(1, `rgba(100, 180, 255, ${opacity})`);

                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = grad;
                        ctx.lineWidth   = opacity * 1.8;

                        ctx.shadowBlur  = 4;
                        ctx.shadowColor = `rgba(100, 180, 255, ${opacity * 0.6})`;
                        ctx.stroke();
                        ctx.shadowBlur  = 0;
                    }
                }
            }
        }

        // ── Animate ───────────────────────────────────────────────────────────
        function animate() {
            // Clear with deep navy fill for "space" feel
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(13, 27, 42, 0.18)'; // slight trail = motion blur effect
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Remove dead burst particles
            particlesArray = particlesArray.filter(p => !p.isDead());

            // Draw connections first (under particles)
            drawConnections();

            // Update + draw each particle
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();
            }

            animFrameId = requestAnimationFrame(animate);
        }

        // ── Boot ──────────────────────────────────────────────────────────────
        initParticles();
        animate();
    });
})();
