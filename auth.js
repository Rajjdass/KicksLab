const express = require('express');
const { body } = require('express-validator');
const { auth, adminAuth } = require('../middleware/auth');
const User = require('../models/User');
const {
  register,
  login,
  getMe
} = require('../controllers/authController');

const router = express.Router();

// @route   POST /api/auth/register
// @desc    Register user
// @access  Public
router.post('/register', [
  body('name').trim().isLength({ min: 2 }).withMessage('Name must be at least 2 characters'),
  body('email').isEmail().normalizeEmail().withMessage('Please provide a valid email'),
  body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
  body('role').optional().isIn(['user', 'shop']).withMessage('Invalid role'),
  body('shopName').optional().trim().isLength({ min: 2 }).withMessage('Shop name must be at least 2 characters')
], register);

// @route   POST /api/auth/login
// @desc    Login user
// @access  Public
router.post('/login', [
  body('email').isEmail().normalizeEmail().withMessage('Please provide a valid email'),
  body('password').exists().withMessage('Password is required')
], login);

// @route   GET /api/auth/me
// @desc    Get current user
// @access  Private
router.get('/me', auth, getMe);

// Update address
router.put('/me/address', auth, async (req, res) => {
  try {
    const { street, city, state, zipCode, country, phone } = req.body
    req.user.address = { street, city, state, zipCode, country, phone }
    await req.user.save()
    res.json({ message: 'Address updated', address: req.user.address })
  } catch (err) {
    console.error('Update address error:', err)
    res.status(500).json({ message: 'Server error' })
  }
})

module.exports = router;
