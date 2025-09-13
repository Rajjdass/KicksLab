const express = require('express');
const { body } = require('express-validator');
const { auth, adminAuth } = require('../middleware/auth');
const {
    getTemplates,
    getTemplateById,
    createTemplate,
    updateTemplate,
    deleteTemplate
} = require('../controllers/customizationController');

const router = express.Router();

// @route   GET /api/customization/templates
// @desc    Get all templates
// @access  Public
router.get('/templates', getTemplates);

// @route   GET /api/customization/templates/:id
// @desc    Get single template
// @access  Public
router.get('/templates/:id', getTemplateById);

// @route   POST /api/customization/templates
// @desc    Create new template
// @access  Private (Admin)
router.post('/templates', [
    adminAuth,
    body('name').trim().isLength({ min: 2 }).withMessage('Name must be at least 2 characters'),
    body('description').trim().isLength({ min: 10 }).withMessage('Description must be at least 10 characters'),
    body('basePrice').isNumeric().withMessage('Base price must be a number'),
    body('category').isIn(['sneakers', 'formal', 'casual', 'sports', 'boots', 'sandals']).withMessage('Invalid category'),
    body('image').isURL().withMessage('Valid image URL is required'),
    body('customizableParts').isArray({ min: 1 }).withMessage('At least one customizable part is required')
], createTemplate);

// @route   PUT /api/customization/templates/:id
// @desc    Update template
// @access  Private (Admin)
router.put('/templates/:id', adminAuth, updateTemplate);

// @route   DELETE /api/customization/templates/:id
// @desc    Delete template
// @access  Private (Admin)
router.delete('/templates/:id', adminAuth, deleteTemplate);

module.exports = router;

