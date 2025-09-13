const express = require('express');
const { body } = require('express-validator');
const { auth } = require('../middleware/auth');
const {
  getCart,
  addToCart,
  updateCartItem,
  removeFromCart,
  clearCart
} = require('../controllers/cartController');

const router = express.Router();

// @route   GET /api/cart
// @desc    Get user's cart
// @access  Private
router.get('/', auth, getCart);

// @route   POST /api/cart
// @desc    Add item to cart
// @access  Private
router.post('/', [
  auth,
  body('shoeId').isMongoId().withMessage('Valid shoe ID is required'),
  body('quantity').isInt({ min: 1 }).withMessage('Quantity must be at least 1'),
  body('size').trim().isLength({ min: 1 }).withMessage('Size is required')
], addToCart);

// @route   PUT /api/cart/:itemId
// @desc    Update cart item quantity
// @access  Private
router.put('/:itemId', [
  auth,
  body('quantity').isInt({ min: 1 }).withMessage('Quantity must be at least 1')
], updateCartItem);

// @route   DELETE /api/cart/:itemId
// @desc    Remove item from cart
// @access  Private
router.delete('/:itemId', auth, removeFromCart);

// @route   DELETE /api/cart
// @desc    Clear entire cart
// @access  Private
router.delete('/', auth, clearCart);

module.exports = router;
