const CustomizationTemplate = require('../models/CustomizationTemplate');

// @desc    Get all customization templates
// @route   GET /api/customization/templates
// @access  Public
const getTemplates = async (req, res) => {
    try {
        const { category } = req.query;

        const filter = { isActive: true };
        if (category) filter.category = category;

        const templates = await CustomizationTemplate.find(filter).sort({ createdAt: -1 });
        res.json(templates);
    } catch (error) {
        console.error('Get templates error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Get single template
// @route   GET /api/customization/templates/:id
// @access  Public
const getTemplateById = async (req, res) => {
    try {
        const template = await CustomizationTemplate.findById(req.params.id);

        if (!template) {
            return res.status(404).json({ message: 'Template not found' });
        }

        res.json(template);
    } catch (error) {
        console.error('Get template error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Create new template
// @route   POST /api/customization/templates
// @access  Private (Admin)
const createTemplate = async (req, res) => {
    try {
        const template = new CustomizationTemplate(req.body);
        await template.save();
        res.status(201).json(template);
    } catch (error) {
        console.error('Create template error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Update template
// @route   PUT /api/customization/templates/:id
// @access  Private (Admin)
const updateTemplate = async (req, res) => {
    try {
        const template = await CustomizationTemplate.findByIdAndUpdate(
            req.params.id,
            req.body,
            { new: true }
        );

        if (!template) {
            return res.status(404).json({ message: 'Template not found' });
        }

        res.json(template);
    } catch (error) {
        console.error('Update template error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

// @desc    Delete template
// @route   DELETE /api/customization/templates/:id
// @access  Private (Admin)
const deleteTemplate = async (req, res) => {
    try {
        const template = await CustomizationTemplate.findByIdAndDelete(req.params.id);

        if (!template) {
            return res.status(404).json({ message: 'Template not found' });
        }

        res.json({ message: 'Template deleted successfully' });
    } catch (error) {
        console.error('Delete template error:', error);
        res.status(500).json({ message: 'Server error' });
    }
};

module.exports = {
    getTemplates,
    getTemplateById,
    createTemplate,
    updateTemplate,
    deleteTemplate
};

