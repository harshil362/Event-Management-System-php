// Admin Panel Enhanced JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all enhanced features
    
    // Enhanced Tooltips
    initEnhancedTooltips();
    
    // Table Row Selection
    initTableRowSelection();
    
    // Quick Filters
    initQuickFilters();
    
    // Export Functionality
    initExportFeatures();
    
    // Chart Animations
    initChartAnimations();
    
    // Real-time Updates
    initRealTimeUpdates();
    
    // Search with Debounce
    initDebouncedSearch();
    
    // Bulk Actions
    initBulkActions();
    
    // Status Toggle Effects
    initStatusToggleEffects();
});

// Enhanced Tooltips with Animation
function initEnhancedTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip], .btn-action, .status-badge');
    
    tooltipTriggers.forEach(trigger => {
        let tooltip = null;
        let timeout = null;
        
        trigger.addEventListener('mouseenter', function(e) {
            const title = this.getAttribute('title') || this.getAttribute('data-tooltip');
            if(!title) return;
            
            // Clear existing tooltip
            if(tooltip) {
                tooltip.remove();
            }
            
            // Create tooltip
            tooltip = document.createElement('div');
            tooltip.className = 'enhanced-tooltip';
            tooltip.textContent = title;
            document.body.appendChild(tooltip);
            
            // Position tooltip
            const rect = this.getBoundingClientRect();
            const tooltipHeight = tooltip.offsetHeight;
            const tooltipWidth = tooltip.offsetWidth;
            
            let top = rect.top - tooltipHeight - 10;
            let left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
            
            // Adjust if tooltip goes off screen
            if(top < 10) top = rect.bottom + 10;
            if(left < 10) left = 10;
            if(left + tooltipWidth > window.innerWidth - 10) {
                left = window.innerWidth - tooltipWidth - 10;
            }
            
            tooltip.style.cssText = `
                position: fixed;
                top: ${top}px;
                left: ${left}px;
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
                color: white;
                padding: 10px 16px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 500;
                z-index: 10000;
                pointer-events: none;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
                opacity: 0;
                transform: translateY(10px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                max-width: 200px;
                white-space: nowrap;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            `;
            
            // Animate in
            setTimeout(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(0)';
            }, 10);
        });
        
        trigger.addEventListener('mouseleave', function() {
            if(tooltip) {
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(10px)';
                
                timeout = setTimeout(() => {
                    if(tooltip) {
                        tooltip.remove();
                        tooltip = null;
                    }
                }, 300);
            }
        });
        
        // Cleanup
        trigger.addEventListener('click', function() {
            if(tooltip) {
                tooltip.remove();
                tooltip = null;
            }
            if(timeout) clearTimeout(timeout);
        });
    });
}

// Table Row Selection with Visual Feedback
function initTableRowSelection() {
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    let selectedRows = new Set();
    
    tableRows.forEach(row => {
        // Add selection checkbox if not exists
        if(!row.querySelector('.row-selector')) {
            const firstCell = row.querySelector('td:first-child');
            if(firstCell && !firstCell.querySelector('.row-selector')) {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'row-selector';
                checkbox.style.cssText = `
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                    margin-right: 8px;
                    vertical-align: middle;
                `;
                
                const wrapper = document.createElement('div');
                wrapper.style.display = 'inline-block';
                wrapper.appendChild(checkbox);
                
                const originalContent = firstCell.innerHTML;
                firstCell.innerHTML = '';
                firstCell.appendChild(wrapper);
                firstCell.insertAdjacentHTML('beforeend', originalContent);
            }
        }
        
        // Click to select with visual feedback
        row.addEventListener('click', function(e) {
            if(e.target.type === 'checkbox' || e.target.classList.contains('row-selector')) return;
            
            const checkbox = this.querySelector('.row-selector');
            if(checkbox) {
                checkbox.checked = !checkbox.checked;
                toggleRowSelection(this, checkbox.checked);
            }
        });
        
        // Checkbox change
        const checkbox = row.querySelector('.row-selector');
        if(checkbox) {
            checkbox.addEventListener('change', function() {
                toggleRowSelection(row, this.checked);
            });
        }
    });
    
    function toggleRowSelection(row, selected) {
        if(selected) {
            row.style.background = 'linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(102, 126, 234, 0.05) 100%)';
            row.style.borderLeft = '4px solid var(--primary)';
            selectedRows.add(row);
        } else {
            row.style.background = '';
            row.style.borderLeft = '';
            selectedRows.delete(row);
        }
        
        updateBulkActions(selectedRows.size);
    }
}

// Quick Filters with Animation
function initQuickFilters() {
    const filterChips = document.querySelectorAll('.filter-chip');
    const filterForm = document.querySelector('.filter-form');
    
    filterChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            const filterType = this.getAttribute('data-filter-type');
            
            // Toggle active state
            this.classList.toggle('active');
            
            // If form exists, update it
            if(filterForm) {
                let isActive = this.classList.contains('active');
                
                if(isActive) {
                    // Add filter
                    addFilterToForm(filterType, filterValue);
                } else {
                    // Remove filter
                    removeFilterFromForm(filterType, filterValue);
                }
                
                // Submit form after delay
                setTimeout(() => {
                    filterForm.submit();
                }, 300);
            }
            
            // Visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
}

function addFilterToForm(type, value) {
    const form = document.querySelector('.filter-form');
    let existingInput = form.querySelector(`input[name="${type}"]`);
    
    if(!existingInput) {
        existingInput = document.createElement('input');
        existingInput.type = 'hidden';
        existingInput.name = type;
        existingInput.value = value;
        form.appendChild(existingInput);
    } else {
        // Handle multiple values
        const currentValue = existingInput.value;
        if(!currentValue.includes(value)) {
            existingInput.value = currentValue ? `${currentValue},${value}` : value;
        }
    }
}

function removeFilterFromForm(type, value) {
    const form = document.querySelector('.filter-form');
    const input = form.querySelector(`input[name="${type}"]`);
    
    if(input) {
        const values = input.value.split(',');
        const index = values.indexOf(value);
        if(index > -1) {
            values.splice(index, 1);
            if(values.length > 0) {
                input.value = values.join(',');
            } else {
                input.remove();
            }
        }
    }
}

// Export Functionality with Loading States
function initExportFeatures() {
    const exportButtons = document.querySelectorAll('.btn-export');
    
    exportButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if(this.hasAttribute('data-confirm')) {
                e.preventDefault();
                const confirmMessage = this.getAttribute('data-confirm') || 'Are you sure you want to export this data?';
                
                if(confirm(confirmMessage)) {
                    showLoading(this, 'Exporting...');
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 1000);
                }
            }
        });
    });
}

// Chart Animations and Interactivity
function initChartAnimations() {
    // Initialize any charts on the page
    const charts = document.querySelectorAll('canvas');
    
    charts.forEach(chart => {
        const ctx = chart.getContext('2d');
        
        // Add hover effects
        chart.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        chart.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Real-time Updates Simulation
function initRealTimeUpdates() {
    // Simulate real-time updates for stats
    const updateStats = () => {
        const statCards = document.querySelectorAll('.stat-card');
        
        statCards.forEach(card => {
            const statValue = card.querySelector('.stat-info h3');
            if(statValue && Math.random() > 0.7) {
                const currentValue = parseInt(statValue.textContent.replace(/,/g, ''));
                const change = Math.floor(Math.random() * 10) - 2; // -2 to +7
                const newValue = Math.max(0, currentValue + change);
                
                // Animate value change
                animateValueChange(statValue, currentValue, newValue);
                
                // Add trend indicator
                addTrendIndicator(card, change);
            }
        });
    };
    
    // Update every 30 seconds
    setInterval(updateStats, 30000);
}

function animateValueChange(element, start, end) {
    const duration = 1000;
    const startTime = Date.now();
    
    const animate = () => {
        const now = Date.now();
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = easeOutCubic(progress);
        const value = Math.floor(start + (end - start) * eased);
        
        element.textContent = value.toLocaleString();
        
        if(progress < 1) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
}

function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
}

function addTrendIndicator(card, change) {
    let trendElement = card.querySelector('.stat-trend');
    
    if(!trendElement) {
        trendElement = document.createElement('div');
        trendElement.className = 'stat-trend';
        card.querySelector('.stat-info').appendChild(trendElement);
    }
    
    if(change > 0) {
        trendElement.className = 'stat-trend trend-up';
        trendElement.innerHTML = `<i class="fas fa-arrow-up"></i> ${Math.abs(change)}`;
    } else if(change < 0) {
        trendElement.className = 'stat-trend trend-down';
        trendElement.innerHTML = `<i class="fas fa-arrow-down"></i> ${Math.abs(change)}`;
    } else {
        trendElement.className = 'stat-trend';
        trendElement.innerHTML = `<i class="fas fa-minus"></i> 0`;
    }
}

// Debounced Search for Better Performance
function initDebouncedSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    
    if(searchInput) {
        let timeout = null;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            
            // Show loading state
            this.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"%2364748b\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"feather feather-search\"%3E%3Ccircle cx=\"11\" cy=\"11\" r=\"8\"%3E%3C/circle%3E%3Cline x1=\"21\" y1=\"21\" x2=\"16.65\" y2=\"16.65\"%3E%3C/line%3E%3C/svg%3E")';
            this.style.backgroundRepeat = 'no-repeat';
            this.style.backgroundPosition = 'right 12px center';
            this.style.backgroundSize = '20px';
            
            timeout = setTimeout(() => {
                // Submit form
                const form = this.closest('form');
                if(form) {
                    form.submit();
                }
            }, 500);
        });
    }
}

// Bulk Actions with Confirmation
function initBulkActions() {
    const bulkActionsContainer = document.querySelector('.bulk-actions');
    
    if(!bulkActionsContainer) {
        // Create bulk actions container
        const container = document.createElement('div');
        container.className = 'bulk-actions';
        container.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 15px;
            border: 1px solid #e2e8f0;
            backdrop-filter: blur(10px);
        `;
        
        container.innerHTML = `
            <span class="selected-count" style="font-weight: 600; color: #334155;">0 selected</span>
            <div class="bulk-buttons" style="display: flex; gap: 10px;">
                <button class="btn btn-danger btn-sm bulk-delete" style="padding: 8px 16px;">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button class="btn btn-primary btn-sm bulk-export" style="padding: 8px 16px;">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn btn-secondary btn-sm bulk-clear" style="padding: 8px 16px;">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        `;
        
        document.body.appendChild(container);
    }
}

function updateBulkActions(count) {
    const container = document.querySelector('.bulk-actions');
    const countElement = container.querySelector('.selected-count');
    
    if(count > 0) {
        container.style.display = 'flex';
        countElement.textContent = `${count} selected`;
        
        // Add animation
        container.style.animation = 'slideUp 0.3s ease';
    } else {
        container.style.display = 'none';
    }
    
    // Add bulk action handlers
    const bulkDelete = container.querySelector('.bulk-delete');
    const bulkExport = container.querySelector('.bulk-export');
    const bulkClear = container.querySelector('.bulk-clear');
    
    if(bulkDelete) {
        bulkDelete.onclick = () => handleBulkDelete();
    }
    
    if(bulkExport) {
        bulkExport.onclick = () => handleBulkExport();
    }
    
    if(bulkClear) {
        bulkClear.onclick = () => clearAllSelections();
    }
}

function handleBulkDelete() {
    const selectedRows = document.querySelectorAll('.row-selector:checked');
    const ids = Array.from(selectedRows).map(checkbox => {
        const row = checkbox.closest('tr');
        return row.querySelector('td:first-child').textContent.replace('#', '');
    });
    
    if(confirm(`Delete ${ids.length} selected items?`)) {
        showLoading(document.body, 'Deleting...');
        // In real implementation, make AJAX call
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
}

function handleBulkExport() {
    const selectedRows = document.querySelectorAll('.row-selector:checked');
    const ids = Array.from(selectedRows).map(checkbox => {
        const row = checkbox.closest('tr');
        return row.querySelector('td:first-child').textContent.replace('#', '');
    });
    
    showLoading(document.body, 'Preparing export...');
    // In real implementation, make AJAX call
    setTimeout(() => {
        alert(`Exporting ${ids.length} items...`);
    }, 1000);
}

function clearAllSelections() {
    document.querySelectorAll('.row-selector').forEach(checkbox => {
        checkbox.checked = false;
        const row = checkbox.closest('tr');
        row.style.background = '';
        row.style.borderLeft = '';
    });
    
    document.querySelector('.bulk-actions').style.display = 'none';
}

// Status Toggle Effects
function initStatusToggleEffects() {
    const statusToggles = document.querySelectorAll('[data-status-toggle]');
    
    statusToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if(this.tagName === 'FORM') {
                e.preventDefault();
                
                const form = this;
                const statusBadge = form.closest('tr').querySelector('.status-badge');
                
                if(statusBadge) {
                    // Animate status change
                    statusBadge.style.transform = 'scale(1.1)';
                    statusBadge.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        statusBadge.style.transform = 'scale(1)';
                    }, 300);
                }
                
                // Show confirmation for certain actions
                const action = form.querySelector('input[name="action"]').value;
                const userId = form.querySelector('input[name="user_id"]').value;
                
                let confirmMessage = '';
                switch(action) {
                    case 'delete':
                        confirmMessage = 'Are you sure you want to delete this user?';
                        break;
                    case 'toggle_status':
                        confirmMessage = 'Change user status?';
                        break;
                    case 'make_admin':
                        confirmMessage = 'Make this user an administrator?';
                        break;
                    case 'remove_admin':
                        confirmMessage = 'Remove administrator privileges?';
                        break;
                }
                
                if(confirmMessage && !confirm(confirmMessage)) {
                    return;
                }
                
                showLoading(this, 'Processing...');
                
                // Submit form after delay
                setTimeout(() => {
                    form.submit();
                }, 800);
            }
        });
    });
}

// Loading State Helper
function showLoading(element, message = 'Loading...') {
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading-spinner';
    loadingDiv.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 15px;
        z-index: 1000;
        border-radius: inherit;
        backdrop-filter: blur(5px);
    `;
    
    loadingDiv.innerHTML = `
        <div class="spinner" style="width: 50px; height: 50px;"></div>
        <span style="color: #64748b; font-weight: 600;">${message}</span>
    `;
    
    element.style.position = 'relative';
    element.appendChild(loadingDiv);
    
    return loadingDiv;
}

// Remove Loading State
function hideLoading(element) {
    const loadingDiv = element.querySelector('.loading-spinner');
    if(loadingDiv) {
        loadingDiv.remove();
    }
}

// CSS Animation for slideUp
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .slide-up {
        animation: slideUp 0.3s ease;
    }
    
    .slide-in-right {
        animation: slideInRight 0.3s ease;
    }
`;
document.head.appendChild(style);