// Performance monitoring and optimization
class PerformanceOptimizer {
    
    constructor() {
        this.initLazyLoading();
        this.initImageOptimization();
        this.monitorPerformance();
    }
    
    // Lazy loading for images
    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
    
    // Optimize images on load
    initImageOptimization() {
        // Convert regular img tags to lazy loading
        document.querySelectorAll('img:not([data-src]):not([loading])').forEach(img => {
            img.setAttribute('loading', 'lazy');
        });
    }
    
    // Monitor Core Web Vitals
    monitorPerformance() {
        if ('PerformanceObserver' in window) {
            try {
                // Largest Contentful Paint
                new PerformanceObserver((entryList) => {
                    for (const entry of entryList.getEntries()) {
                        console.log('LCP:', entry.startTime);
                    }
                }).observe({entryTypes: ['largest-contentful-paint']});
                
                // First Input Delay
                new PerformanceObserver((entryList) => {
                    for (const entry of entryList.getEntries()) {
                        console.log('FID:', entry.processingStart - entry.startTime);
                    }
                }).observe({entryTypes: ['first-input']});
                
                // Cumulative Layout Shift
                new PerformanceObserver((entryList) => {
                    for (const entry of entryList.getEntries()) {
                        if (!entry.hadRecentInput) {
                            console.log('CLS:', entry.value);
                        }
                    }
                }).observe({entryTypes: ['layout-shift']});
            } catch (e) {
                console.log('Performance monitoring not supported');
            }
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PerformanceOptimizer();
});
