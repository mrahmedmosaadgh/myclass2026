import confetti from 'canvas-confetti'

export const useConfetti = () => {
    const defaultColors = ['#FFD700', '#FF69B4', '#00CED1', '#9370DB']

    const launchConfetti = ({
        // Default configuration options
        colors = defaultColors,
        particleCount = 80,
        spread = 55,
        origin = { y: 0.7 },
        gravity = 1.2,
        scalar = 1.2,
        ticks = 150,
        sideBursts = true,
        delay = 100
    } = {}) => {
        // Main burst
        confetti({
            particleCount,
            spread,
            origin,
            colors,
            gravity,
            scalar,
            ticks
        })

        // Side bursts
        if (sideBursts) {
            setTimeout(() => {
                // Left burst
                confetti({
                    particleCount: particleCount / 2.5,
                    angle: 60,
                    spread: 40,
                    origin: { x: 0, y: 0.8 },
                    colors
                })

                // Right burst
                confetti({
                    particleCount: particleCount / 2.5,
                    angle: 120,
                    spread: 40,
                    origin: { x: 1, y: 0.8 },
                    colors
                })
            }, delay)
        }
    }

    // Predefined animation patterns
    const patterns = {
        victory: () => launchConfetti({
            colors: ['#FFD700', '#FFA500', '#FF4500'],
            particleCount: 100,
            spread: 70,
            gravity: 0.8
        }),

        celebration: () => launchConfetti({
            colors: ['#FF69B4', '#9370DB', '#00CED1'],
            particleCount: 120,
            spread: 60
        }),

        fireworks: () => {
            const launches = 3
            for (let i = 0; i < launches; i++) {
                setTimeout(() => {
                    launchConfetti({
                        colors: ['#FF0000', '#FFFFFF', '#0000FF'],
                        particleCount: 50,
                        spread: 45,
                        origin: { y: 0.85 },
                        sideBursts: false
                    })
                }, i * 200)
            }
        },

        rain: () => launchConfetti({
            colors: ['#1E90FF', '#00BFFF'],
            particleCount: 150,
            spread: 180,
            ticks: 400,
            origin: { y: -0.1 },
            gravity: 0.8,
            scalar: 0.8,
            sideBursts: false
        })
    }

    return {
        launchConfetti,
        patterns
    }
}
