// resources/js/pwa.js
// PWA Registration and Setup

export function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', async () => {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js')
                console.log('SW registered: ', registration)

                // Handle service worker updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed') {
                                if (navigator.serviceWorker.controller) {
                                    // New content is available; please refresh
                                    showUpdateAvailable()
                                } else {
                                    // Content is cached for offline use
                                    console.log('Content is cached for offline use.')
                                }
                            }
                        })
                    }
                })
            } catch (error) {
                console.log('SW registration failed: ', error)
            }
        })
    }
}

function showUpdateAvailable() {
    // Show update notification
    const updateBanner = document.createElement('div')
    updateBanner.innerHTML = `
    <div style="
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: #226f54;
      color: white;
      padding: 12px;
      text-align: center;
      z-index: 9999;
      font-family: Arial, sans-serif;
    ">
      <span>A new version is available!</span>
      <button onclick="window.location.reload()" style="
        margin-left: 12px;
        background: white;
        color: #226f54;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
      ">
        Update Now
      </button>
      <button onclick="this.parentElement.remove()" style="
        margin-left: 8px;
        background: transparent;
        color: white;
        border: 1px solid white;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
      ">
        Later
      </button>
    </div>
  `
    document.body.appendChild(updateBanner)
}

// Add to head of your main layout
export function addPWAMetaTags() {
    const head = document.head

    // Add meta tags for PWA
    const metaTags = [
        { name: 'theme-color', content: '#226f54' },
        { name: 'apple-mobile-web-app-capable', content: 'yes' },
        { name: 'apple-mobile-web-app-status-bar-style', content: 'default' },
        { name: 'apple-mobile-web-app-title', content: 'ExpenseTracker' },
        { name: 'application-name', content: 'ExpenseTracker' },
        { name: 'msapplication-TileColor', content: '#226f54' },
        { name: 'msapplication-config', content: '/browserconfig.xml' }
    ]

    metaTags.forEach(tag => {
        const meta = document.createElement('meta')
        meta.name = tag.name
        meta.content = tag.content
        head.appendChild(meta)
    })

    // Add manifest link
    const manifestLink = document.createElement('link')
    manifestLink.rel = 'manifest'
    manifestLink.href = '/manifest.json'
    head.appendChild(manifestLink)

    // Add apple touch icons
    const appleTouchIcon = document.createElement('link')
    appleTouchIcon.rel = 'apple-touch-icon'
    appleTouchIcon.href = '/icons/icon-192x192.png'
    head.appendChild(appleTouchIcon)
}

// Install prompt handling
export function setupInstallPrompt() {
    let deferredPrompt

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault()
        // Stash the event so it can be triggered later
        deferredPrompt = e

        // Show install button
        showInstallButton(deferredPrompt)
    })

    window.addEventListener('appinstalled', () => {
        console.log('PWA was installed')
        hideInstallButton()
    })
}

function showInstallButton(prompt) {
    // Create install button
    const installButton = document.createElement('button')
    installButton.id = 'pwa-install-button'
    installButton.innerHTML = `
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
      <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
    </svg>
    Install App
  `
    installButton.style.cssText = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #226f54;
    color: white;
    border: none;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    transition: all 0.2s ease;
  `

    installButton.addEventListener('mouseenter', () => {
        installButton.style.background = '#1a5440'
        installButton.style.transform = 'translateY(-2px)'
    })

    installButton.addEventListener('mouseleave', () => {
        installButton.style.background = '#226f54'
        installButton.style.transform = 'translateY(0)'
    })

    installButton.addEventListener('click', async () => {
        if (prompt) {
            prompt.prompt()
            const { outcome } = await prompt.userChoice
            console.log(`User response to the install prompt: ${outcome}`)
            deferredPrompt = null
            hideInstallButton()
        }
    })

    document.body.appendChild(installButton)
}

function hideInstallButton() {
    const button = document.getElementById('pwa-install-button')
    if (button) {
        button.remove()
    }
}

// Initialize PWA features
export function initPWA() {
    addPWAMetaTags()
    registerServiceWorker()
    setupInstallPrompt()

    // Add offline/online event listeners
    window.addEventListener('online', () => {
        console.log('App is online')
        document.body.classList.remove('offline')
        document.body.classList.add('online')
    })

    window.addEventListener('offline', () => {
        console.log('App is offline')
        document.body.classList.remove('online')
        document.body.classList.add('offline')
    })

    // Initial state
    if (navigator.onLine) {
        document.body.classList.add('online')
    } else {
        document.body.classList.add('offline')
    }
}
