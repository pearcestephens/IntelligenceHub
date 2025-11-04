/**
 * Visual Workflow Builder
 *
 * Drag-drop interface for creating frontend automation workflows
 * Integrates with AI Agent ToolChainOrchestrator
 *
 * @version 1.0.0
 * @date 2025-11-04
 */

class WorkflowBuilder {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('Workflow builder container not found:', containerId);
            return;
        }

        this.canvas = null;
        this.nodes = [];
        this.connections = [];
        this.selectedNode = null;
        this.draggedNode = null;
        this.currentWorkflowId = null;

        this.init();
    }

    init() {
        this.setupCanvas();
        this.setupToolbar();
        this.setupEventListeners();
        this.loadAvailableTools();
    }

    setupCanvas() {
        this.canvas = document.createElement('div');
        this.canvas.className = 'workflow-canvas';
        this.canvas.id = 'workflow-canvas';
        this.canvas.style.cssText = `
            position: relative;
            width: 100%;
            min-height: 600px;
            background:
                linear-gradient(90deg, rgba(0,0,0,.03) 1px, transparent 1px),
                linear-gradient(rgba(0,0,0,.03) 1px, transparent 1px);
            background-size: 20px 20px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: auto;
        `;
        this.container.appendChild(this.canvas);
    }

    setupToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'workflow-toolbar mb-3 p-3 bg-light border rounded';
        toolbar.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-primary" onclick="workflowBuilder.addNode('audit')">
                        <i class="bi bi-search"></i> Audit Page
                    </button>
                    <button class="btn btn-sm btn-success" onclick="workflowBuilder.addNode('fix')">
                        <i class="bi bi-wrench"></i> Auto-Fix
                    </button>
                    <button class="btn btn-sm btn-info" onclick="workflowBuilder.addNode('screenshot')">
                        <i class="bi bi-camera"></i> Screenshot
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="workflowBuilder.addNode('monitor')">
                        <i class="bi bi-eye"></i> Monitor
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="workflowBuilder.addNode('condition')">
                        <i class="bi bi-arrows-angle-expand"></i> Condition
                    </button>
                </div>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary" onclick="workflowBuilder.load()">
                        <i class="bi bi-folder-open"></i> Load
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="workflowBuilder.save()">
                        <i class="bi bi-save"></i> Save
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="workflowBuilder.clear()">
                        <i class="bi bi-trash"></i> Clear
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="workflowBuilder.execute()">
                        <i class="bi bi-play-fill"></i> Run Workflow
                    </button>
                </div>
            </div>
        `;
        this.container.insertBefore(toolbar, this.canvas);
    }

    setupEventListeners() {
        // Canvas click for deselect
        this.canvas.addEventListener('click', (e) => {
            if (e.target === this.canvas) {
                this.deselectAll();
            }
        });
    }

    loadAvailableTools() {
        // Could fetch from backend, for now hardcoded
        this.availableTools = {
            'audit': {
                name: 'Audit Page',
                icon: '🔍',
                color: '#0d6efd',
                params: {
                    url: 'https://staff.vapeshed.co.nz',
                    checks: ['errors', 'performance'],
                    auto_fix: false
                }
            },
            'fix': {
                name: 'Auto-Fix',
                icon: '🔧',
                color: '#198754',
                params: {
                    approval_required: true,
                    run_tests: true
                }
            },
            'screenshot': {
                name: 'Screenshot',
                icon: '📸',
                color: '#0dcaf0',
                params: {
                    type: 'full_page',
                    upload: true
                }
            },
            'monitor': {
                name: 'Monitor',
                icon: '👁️',
                color: '#ffc107',
                params: {
                    interval: '5m',
                    checks: ['errors', 'performance'],
                    alert_channels: ['email']
                }
            },
            'condition': {
                name: 'Condition',
                icon: '🔀',
                color: '#6c757d',
                params: {
                    field: 'errors.total',
                    operator: '>',
                    value: 0
                }
            }
        };
    }

    addNode(type) {
        const tool = this.availableTools[type];
        if (!tool) return;

        const node = {
            id: `node_${Date.now()}`,
            type: type,
            x: 100 + (this.nodes.length * 50),
            y: 100 + (Math.floor(this.nodes.length / 5) * 150),
            config: { ...tool.params }
        };

        this.nodes.push(node);
        this.renderNode(node);
    }

    renderNode(node) {
        const tool = this.availableTools[node.type];

        const nodeEl = document.createElement('div');
        nodeEl.className = 'workflow-node';
        nodeEl.id = node.id;
        nodeEl.style.cssText = `
            position: absolute;
            left: ${node.x}px;
            top: ${node.y}px;
            width: 220px;
            padding: 15px;
            background: white;
            border: 2px solid ${tool.color};
            border-radius: 8px;
            cursor: move;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            user-select: none;
        `;

        nodeEl.innerHTML = `
            <div class="node-header d-flex justify-content-between align-items-center mb-2">
                <strong style="color: ${tool.color}">
                    ${tool.icon} ${tool.name}
                </strong>
                <button class="btn btn-sm btn-link text-danger p-0" onclick="workflowBuilder.deleteNode('${node.id}')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="node-body" style="font-size: 0.85rem;">
                ${this.renderNodeConfig(node)}
            </div>
            <div class="node-ports">
                <div class="port port-input" data-node="${node.id}" data-type="input"
                     style="position: absolute; left: -10px; top: 50%; width: 20px; height: 20px;
                            background: ${tool.color}; border-radius: 50%; border: 2px solid white;
                            cursor: pointer;"></div>
                <div class="port port-output" data-node="${node.id}" data-type="output"
                     style="position: absolute; right: -10px; top: 50%; width: 20px; height: 20px;
                            background: ${tool.color}; border-radius: 50%; border: 2px solid white;
                            cursor: pointer;"></div>
            </div>
        `;

        this.canvas.appendChild(nodeEl);
        this.makeDraggable(nodeEl, node);
    }

    renderNodeConfig(node) {
        let html = '<div class="node-config small">';

        for (const [key, value] of Object.entries(node.config)) {
            const inputType = typeof value === 'boolean' ? 'checkbox' : 'text';
            const inputValue = typeof value === 'boolean' ? '' : (Array.isArray(value) ? value.join(', ') : value);

            if (typeof value === 'boolean') {
                html += `
                    <div class="form-check mb-1">
                        <input type="checkbox" class="form-check-input"
                               ${value ? 'checked' : ''}
                               onchange="workflowBuilder.updateNodeConfig('${node.id}', '${key}', this.checked)">
                        <label class="form-check-label">${key}</label>
                    </div>
                `;
            } else {
                html += `
                    <div class="mb-2">
                        <label class="form-label small mb-0">${key}:</label>
                        <input type="text" class="form-control form-control-sm"
                               value="${inputValue}"
                               onchange="workflowBuilder.updateNodeConfig('${node.id}', '${key}', this.value)">
                    </div>
                `;
            }
        }

        html += '</div>';
        return html;
    }

    updateNodeConfig(nodeId, key, value) {
        const node = this.nodes.find(n => n.id === nodeId);
        if (!node) return;

        // Parse arrays
        if (typeof node.config[key] === 'object' && !Array.isArray(node.config[key])) {
            // Keep as is
        } else if (typeof node.config[key] === 'object') {
            // Array
            node.config[key] = value.split(',').map(v => v.trim());
        } else {
            node.config[key] = value;
        }
    }

    makeDraggable(element, node) {
        let isDragging = false;
        let startX, startY;

        element.addEventListener('mousedown', (e) => {
            if (e.target.closest('input, button, .port')) return;

            isDragging = true;
            startX = e.clientX - node.x;
            startY = e.clientY - node.y;
            element.style.zIndex = '1000';
            element.style.boxShadow = '0 4px 16px rgba(0,0,0,0.2)';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            node.x = e.clientX - startX;
            node.y = e.clientY - startY;

            element.style.left = node.x + 'px';
            element.style.top = node.y + 'px';
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                element.style.zIndex = '1';
                element.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            }
        });
    }

    deleteNode(nodeId) {
        if (!confirm('Delete this node?')) return;

        this.nodes = this.nodes.filter(n => n.id !== nodeId);
        this.connections = this.connections.filter(c => c.from !== nodeId && c.to !== nodeId);
        document.getElementById(nodeId)?.remove();
    }

    deselectAll() {
        this.selectedNode = null;
        document.querySelectorAll('.workflow-node').forEach(n => {
            n.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        });
    }

    clear() {
        if (!confirm('Clear all nodes and connections?')) return;

        this.nodes = [];
        this.connections = [];
        this.canvas.innerHTML = '';
    }

    async save() {
        const name = prompt('Workflow name:', 'My Workflow');
        if (!name) return;

        const description = prompt('Description (optional):');

        const workflow = {
            name: name,
            description: description || '',
            workflow_json: {
                nodes: this.nodes,
                connections: this.connections
            },
            tags: []
        };

        try {
            const response = await fetch('/ai-agent/api/save-workflow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(workflow)
            });

            const result = await response.json();

            if (result.success) {
                alert('✅ Workflow saved!');
                this.currentWorkflowId = result.workflow_id;
            } else {
                alert('❌ Error: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            alert('❌ Network error: ' + error.message);
        }
    }

    async load() {
        // Show loading modal with workflow list
        alert('Load workflow feature coming soon! For now, edit workflows.php to pre-populate.');
    }

    async execute() {
        if (this.nodes.length === 0) {
            alert('⚠️ Add some nodes first!');
            return;
        }

        if (!confirm(`🚀 Execute workflow with ${this.nodes.length} steps?`)) {
            return;
        }

        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Running...';

        try {
            const response = await fetch('/ai-agent/api/execute-workflow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    workflow_id: this.currentWorkflowId,
                    nodes: this.nodes,
                    connections: this.connections
                })
            });

            const result = await response.json();

            if (result.success) {
                alert(`✅ Workflow complete!\n\n` +
                      `Duration: ${result.duration_ms}ms\n` +
                      `Steps completed: ${result.steps_completed}/${result.steps_total}\n` +
                      `Steps failed: ${result.steps_failed}\n\n` +
                      `View full results: ${result.dashboard_url}`);
            } else {
                alert('❌ Workflow failed:\n\n' + (result.error || 'Unknown error'));
            }

            btn.disabled = false;
            btn.innerHTML = originalHtml;

        } catch (error) {
            alert('❌ Network error: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}

// Initialize on page load
let workflowBuilder;
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('workflow-builder');
    if (container) {
        workflowBuilder = new WorkflowBuilder('workflow-builder');
    }
});
