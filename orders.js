const express = require('express');
const { body } = require('express-validator');
const { auth, adminAuth, shopAuth } = require('../middleware/auth');
const {
  createOrder,
  getOrders,
  getOrderById,
  adminListOrders,
  adminGetOrderById,
  adminDownloadInvoice,
  shopListOrders,
  shopUpdateOrderStatus,
  shopDownloadInvoice
} = require('../controllers/orderController');

const router = express.Router();

// @route   POST /api/orders
// @desc    Create new order
// @access  Private
router.post('/', [
  auth,
  body('items').isArray({ min: 1 }).withMessage('At least one item is required'),
  body('shippingAddress.street').trim().isLength({ min: 5 }).withMessage('Street address is required'),
  body('shippingAddress.city').trim().isLength({ min: 2 }).withMessage('City is required'),
  body('shippingAddress.state').trim().isLength({ min: 2 }).withMessage('State is required'),
  body('shippingAddress.zipCode').trim().isLength({ min: 5 }).withMessage('Valid zip code is required'),
  body('shippingAddress.country').trim().isLength({ min: 2 }).withMessage('Country is required'),
  body('paymentMethod').isIn(['stripe', 'paypal', 'cash']).withMessage('Invalid payment method')
], createOrder);

// Shop owner: list own shop's orders and update status
// We will expose orders by filtering items.shoe.shop == req.user._id
// Implemented as a dedicated controller below using aggregation.

// Admin routes
// @route   GET /api/orders/admin
// @desc    List all orders
// @access  Private (Admin)
router.get('/admin', adminAuth, adminListOrders);

// @route   GET /api/orders/admin/:id
// @desc    Get order by id (admin)
// @access  Private (Admin)
router.get('/admin/:id', adminAuth, adminGetOrderById);

// @route   GET /api/orders/admin/:id/invoice
// @desc    Download invoice PDF
// @access  Private (Admin)
router.get('/admin/:id/invoice', adminAuth, adminDownloadInvoice);

// @route   GET /api/orders/shop
// @desc    List orders for this shop owner
// @access  Private (Shop)
router.get('/shop', shopAuth, shopListOrders);

// @route   PATCH /api/orders/shop/:id/status
// @desc    Update order status by shop owner
// @access  Private (Shop)
router.patch('/shop/:id/status', shopAuth, shopUpdateOrderStatus);

// @route   GET /api/orders/shop/:id/invoice
// @desc    Download invoice PDF (shop owner)
// @access  Private (Shop)
router.get('/shop/:id/invoice', shopAuth, shopDownloadInvoice);

// @route   GET /api/orders
// @desc    Get user's orders
// @access  Private
router.get('/', auth, getOrders);

// @route   GET /api/orders/:id
// @desc    Get single order
// @access  Private
router.get('/:id', auth, getOrderById);

module.exports = router;