const express = require('express');
const { body } = require('express-validator');
const { auth, shopAuth, adminAuth } = require('../middleware/auth');
const {
  getShoes,
  getShoeById,
  createShoe,
  updateShoe,
  deleteShoe,
  getCategories,
  getBrands,
  getShopStats,
  getMyShoes,
  addShoeReview
} = require('../controllers/shoeController');

const router = express.Router();

// @route   GET /api/shoes
// @desc    Get all shoes with filters
// @access  Public
router.get('/', getShoes);

// @route   GET /api/shoes/categories
// @desc    Get all categories
// @access  Public
router.get('/categories', getCategories);

// @route   GET /api/shoes/brands
// @desc    Get all brands
// @access  Public
router.get('/brands', getBrands);

// @route   GET /api/shoes/shop-stats
// @desc    Get shop statistics
// @access  Private (Shop)
router.get('/shop-stats', shopAuth, getShopStats);

// @route   GET /api/shoes/mine
// @desc    Get shoes owned by current shop
// @access  Private (Shop)
router.get('/mine', shopAuth, getMyShoes);

// @route   POST /api/shoes/:id/reviews
// @desc    Add or update a review for a shoe
// @access  Private
router.post('/:id/reviews', auth, addShoeReview);

// @route   GET /api/shoes/:id
// @desc    Get single shoe
// @access  Public
router.get('/:id', getShoeById);

// @route   POST /api/shoes
// @desc    Create new shoe
// @access  Private (Shop)
router.post('/', [
  shopAuth,
  body('name').trim().isLength({ min: 2 }).withMessage('Name must be at least 2 characters'),
  body('brand').trim().isLength({ min: 1 }).withMessage('Brand is required'),
  body('description').trim().isLength({ min: 10 }).withMessage('Description must be at least 10 characters'),
  body('price').isNumeric().withMessage('Price must be a number'),
  body('category').isIn(['sneakers', 'formal', 'casual', 'sports', 'boots', 'sandals']).withMessage('Invalid category'),
  body('gender').isIn(['men', 'women', 'unisex']).withMessage('Invalid gender'),
  body('condition').isIn(['new', 'used', 'refurbished']).withMessage('Invalid condition'),
  body('sizes').isArray({ min: 1 }).withMessage('At least one size is required'),
  body('colors').isArray({ min: 1 }).withMessage('At least one color is required')
], createShoe);

// @route   POST /api/shoes/admin
// @desc    Create new shoe (admin)
// @access  Private (Admin)
router.post('/admin', [
  adminAuth,
  body('name').trim().isLength({ min: 2 }).withMessage('Name must be at least 2 characters'),
  body('brand').trim().isLength({ min: 1 }).withMessage('Brand is required'),
  body('description').trim().isLength({ min: 10 }).withMessage('Description must be at least 10 characters'),
  body('price').isNumeric().withMessage('Price must be a number'),
  body('category').isIn(['sneakers', 'formal', 'casual', 'sports', 'boots', 'sandals']).withMessage('Invalid category'),
  body('gender').isIn(['men', 'women', 'unisex']).withMessage('Invalid gender'),
  body('condition').isIn(['new', 'used', 'refurbished']).withMessage('Invalid condition'),
  body('sizes').isArray({ min: 1 }).withMessage('At least one size is required'),
  body('colors').isArray({ min: 1 }).withMessage('At least one color is required'),
  body('shop').isMongoId().withMessage('Valid shop (seller) id is required')
], createShoe);

// @route   PUT /api/shoes/:id
// @desc    Update shoe
// @access  Private (Shop)
router.put('/:id', shopAuth, updateShoe);

// @route   DELETE /api/shoes/:id
// @desc    Delete shoe
// @access  Private (Shop)
router.delete('/:id', shopAuth, deleteShoe);

module.exports = router;
