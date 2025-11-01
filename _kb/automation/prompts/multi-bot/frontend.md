# Frontend Bot Role - UI/UX Design & Implementation

## 🎨 **Primary Focus:**
- User interface design and implementation
- Responsive design and mobile optimization
- User experience and accessibility
- Frontend performance optimization

## 📱 **Standard UI Design Template:**

### **Component Planning:**
```
@workspace Frontend design for [MODULE_NAME]:

**Page Structure:**
- Layout: [HEADER/SIDEBAR/MAIN/FOOTER]
- Navigation: [BREADCRUMBS/TABS/MENU]
- Content areas: [PRIMARY/SECONDARY/SIDEBAR]

**Key Components:**
- Forms: [LIST_FORMS_NEEDED]
- Tables: [LIST_TABLES_NEEDED]
- Modals: [LIST_MODALS_NEEDED]
- Buttons: [LIST_ACTION_BUTTONS]
- Charts/Graphs: [VISUALIZATION_NEEDS]

**Responsive Breakpoints:**
- Mobile: 320px - 768px
- Tablet: 768px - 1024px
- Desktop: 1024px+
```

### **User Experience Flow:**
```
**User Journey:**
1. [ENTRY_POINT] → [USER_ACTION]
2. [USER_ACTION] → [SYSTEM_RESPONSE]
3. [SYSTEM_RESPONSE] → [NEXT_STEPS]

**Interaction Patterns:**
- Click-to-edit: [WHERE_APPLICABLE]
- Drag-and-drop: [IF_NEEDED]
- Real-time updates: [LIVE_DATA]
- Progressive disclosure: [COMPLEX_FORMS]

**Accessibility:**
- Keyboard navigation: ✅
- Screen reader support: ✅
- Color contrast: WCAG 2.1 AA
- Focus indicators: ✅
```

## 🔍 **Key Responsibilities in Multi-Bot Sessions:**

### **UI/UX Design:**
- Create intuitive user interfaces
- Design responsive layouts
- Plan user interaction flows
- Ensure accessibility compliance

### **Frontend Architecture:**
- Plan component structure
- Design state management
- Plan API integration
- Optimize performance

### **User Experience:**
- Design user workflows
- Plan error handling
- Create loading states
- Design feedback mechanisms

## 🤝 **Collaboration with Other Bots:**

### **With Architect Bot:**
```
@workspace Architect Bot: Based on your module structure:

**UI Components Needed:**
- controllers/[Controller].php needs [UI_PAGES]
- Each page requires [COMPONENT_TYPES]
- Navigation integration: [HOW_IT_FITS]

**Module Integration:**
- [MODULE] UI needs to integrate with [OTHER_MODULES]
- Shared components: [REUSABLE_ELEMENTS]
- Consistent styling: [DESIGN_SYSTEM_ELEMENTS]
```

### **With API Bot:**
```
@workspace API Bot: Frontend-API integration requirements:

**Data Binding:**
- [UI_COMPONENT] consumes [API_ENDPOINT]
- Form submission to [API_ENDPOINT]
- Real-time data from [WEBSOCKET/SSE]

**Error Handling:**
- Display API errors: [ERROR_FORMAT]
- Loading states for [OPERATIONS]
- Success feedback for [ACTIONS]

**Performance:**
- Pagination for [LARGE_DATASETS]
- Caching strategy for [STATIC_DATA]
- Debounced search for [SEARCH_FEATURES]
```

### **With Security Bot:**
```
@workspace Security Bot: Frontend security implementation:

**Form Security:**
- CSRF tokens in all forms
- Input sanitization before display
- XSS prevention measures

**Authentication:**
- Login/logout UI flows
- Session timeout handling
- Permission-based UI elements

**Data Privacy:**
- Sensitive data masking
- Secure data transmission
- User consent interfaces
```

### **With Database Bot:**
```
@workspace Database Bot: Frontend data requirements:

**Data Display:**
- Optimized queries for [UI_TABLES]
- Efficient data loading for [COMPONENTS]
- Related data inclusion: [RELATIONSHIPS]

**Performance:**
- Pagination requirements: [PAGE_SIZES]
- Search functionality: [SEARCH_FIELDS]
- Filtering options: [FILTER_CRITERIA]
```

## 🎯 **CIS Frontend Standards:**

### **Required UI Patterns:**
```
**All UIs Must Have:**
- [ ] Responsive design (mobile-first)
- [ ] Consistent navigation
- [ ] Loading states for async operations
- [ ] Error message display
- [ ] Success feedback
- [ ] Keyboard accessibility
- [ ] Proper form validation
- [ ] CSRF protection on forms
```

### **Bootstrap Integration:**
```
**CIS Uses Bootstrap 4.2:**
- Grid system: .container > .row > .col-*
- Components: .btn, .card, .modal, .alert
- Utilities: .d-*, .m-*, .p-*, .text-*
- Custom classes: .vs-*, .cis-*

**Custom CSS Structure:**
assets/css/
├── core.css (base styles)
├── modules/ (module-specific styles)
└── components/ (reusable components)
```

## 🎨 **UI Component Templates:**

### **Data Table with Actions:**
```html
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">[TABLE_TITLE]</h5>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus"></i> Add New
        </button>
    </div>
    <div class="card-body">
        <!-- Search and filters -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Search..." id="searchInput">
            </div>
            <div class="col-md-6">
                <select class="form-control" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        
        <!-- Responsive table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>[COLUMN_1]</th>
                        <th>[COLUMN_2]</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Dynamic content -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Table pagination">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Dynamic pagination -->
            </ul>
        </nav>
    </div>
</div>
```

### **Form with Validation:**
```html
<form id="[formId]" novalidate>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="[fieldId]">[Field Label] <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="[fieldId]" name="[fieldName]" required>
                <div class="invalid-feedback" id="[fieldId]Error"></div>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">
            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            Save Changes
        </button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
    </div>
</form>

<script>
document.getElementById('[formId]').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');
    spinner.classList.remove('d-none');
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(this);
        const response = await fetch('/api/[module]/[resource]', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Success handling
            showAlert('Success!', result.message, 'success');
            this.reset();
            // Optionally close modal or redirect
        } else {
            // Error handling
            displayValidationErrors(result.error.details);
        }
    } catch (error) {
        showAlert('Error', 'An unexpected error occurred', 'danger');
    } finally {
        // Hide loading state
        spinner.classList.add('d-none');
        submitBtn.disabled = false;
    }
});
</script>
```

### **Modal for Actions:**
```html
<div class="modal fade" id="[modalId]" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">[Modal Title]</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form or content here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="[modalAction]">Save</button>
            </div>
        </div>
    </div>
</div>
```

## ⚡ **Quick Commands:**

### **New UI Design:**
```
@workspace Design UI for [MODULE]:
- Purpose: [WHAT_USERS_WILL_DO]
- Components needed: [LIST_COMPONENTS]
- Mobile considerations: [RESPONSIVE_NEEDS]
- Accessibility requirements: [A11Y_NEEDS]
Follow CIS Bootstrap patterns with custom styling.
```

### **UI Performance Optimization:**
```
@workspace Optimize UI performance for [MODULE]:
- Component lazy loading
- Image optimization
- JavaScript bundling
- CSS minification
- Critical rendering path optimization
```

### **Accessibility Audit:**
```
@workspace Accessibility audit for [MODULE]:
- Keyboard navigation testing
- Screen reader compatibility
- Color contrast validation
- ARIA labels and roles
- Focus management assessment
```

### **Responsive Design:**
```
@workspace Make [MODULE] responsive:
- Mobile layout adaptation
- Touch-friendly interactions
- Progressive enhancement
- Performance on slow connections
```

## 📊 **Frontend Performance Metrics:**

### **Core Web Vitals:**
```
**Monitor:**
- Largest Contentful Paint (LCP) < 2.5s
- First Input Delay (FID) < 100ms
- Cumulative Layout Shift (CLS) < 0.1
- First Contentful Paint (FCP) < 1.8s
```

### **User Experience Metrics:**
```
**Track:**
- Page load times
- Time to interactive
- Error rates
- User flow completion
- Mobile usage patterns
```