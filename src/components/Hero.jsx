import React from 'react';
import { motion } from 'framer-motion';
import { FaRocket, FaPlayCircle, FaStar, FaChartLine, FaBox, FaUsers } from 'react-icons/fa';
import CountUp from 'react-countup';

const Hero = () => {
    const stats = [
        { icon: FaUsers, value: 582, label: 'Active Businesses', suffix: '+' },
        { icon: FaChartLine, value: 2480, label: 'Daily Sales', suffix: 'K+' },
        { icon: FaBox, value: 15234, label: 'Products Sold', suffix: '+' },
        { icon: FaStar, value: 99.9, label: 'Uptime', suffix: '%' }
    ];
    
    return (
        <section id="home" className="relative min-h-screen flex items-center overflow-hidden pt-20">
            {/* Animated Background */}
            <div className="absolute inset-0 bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
                <div className="absolute inset-0 opacity-30">
                    <div className="absolute top-20 left-10 w-72 h-72 bg-purple-500 rounded-full filter blur-3xl animate-pulse"></div>
                    <div className="absolute bottom-20 right-10 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl animate-pulse delay-1000"></div>
                </div>
            </div>
            
            <div className="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div className="grid lg:grid-cols-2 gap-12 items-center">
                    {/* Left Content */}
                    <motion.div
                        initial={{ opacity: 0, x: -50 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ duration: 0.8 }}
                        className="text-white"
                    >
                        <div className="inline-flex items-center space-x-2 px-4 py-2 bg-white/20 rounded-full backdrop-blur-sm mb-6">
                            <FaStar className="text-yellow-400" />
                            <span className="text-sm font-semibold">Trusted by 500+ Businesses in Tanzania</span>
                        </div>
                        
                        <h1 className="text-5xl sm:text-6xl lg:text-7xl font-bold leading-tight mb-6">
                            Smartest Way to
                            <span className="block bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">
                                Manage Your Business
                            </span>
                        </h1>
                        
                        <p className="text-xl text-gray-200 mb-8">
                            All-in-one Point of Sale, Inventory Management, and Customer Loyalty System 
                            designed specifically for Tanzanian businesses.
                        </p>
                        
                        <div className="flex flex-wrap gap-4">
                            <a
                                href="admin/index.html"
                                className="inline-flex items-center gap-2 px-8 py-3 bg-white text-purple-600 rounded-xl font-bold hover:shadow-2xl transition transform hover:scale-105"
                            >
                                <FaRocket />
                                Get Started Free
                            </a>
                            <a
                                href="#demo"
                                className="inline-flex items-center gap-2 px-8 py-3 border-2 border-white rounded-xl font-bold hover:bg-white hover:text-purple-600 transition"
                            >
                                <FaPlayCircle />
                                Watch Demo
                            </a>
                        </div>
                        
                        {/* Stats */}
                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-6 mt-12">
                            {stats.map((stat, index) => (
                                <div key={index} className="text-center">
                                    <stat.icon className="text-3xl text-yellow-400 mx-auto mb-2" />
                                    <div className="text-2xl font-bold">
                                        <CountUp end={stat.value} duration={2.5} suffix={stat.suffix} />
                                    </div>
                                    <p className="text-sm text-gray-300">{stat.label}</p>
                                </div>
                            ))}
                        </div>
                    </motion.div>
                    
                    {/* Right Content - Dashboard Preview */}
                    <motion.div
                        initial={{ opacity: 0, x: 50, scale: 0.9 }}
                        animate={{ opacity: 1, x: 0, scale: 1 }}
                        transition={{ duration: 0.8, delay: 0.2 }}
                        className="relative"
                    >
                        <div className="glass rounded-2xl p-6 shadow-2xl floating">
                            <div className="bg-gradient-to-br from-white/10 to-transparent rounded-xl p-6">
                                <div className="flex justify-between items-center mb-6">
                                    <h3 className="text-white font-semibold">Live Dashboard</h3>
                                    <div className="flex space-x-1">
                                        <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                                        <div className="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                        <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div className="space-y-4">
                                    <div className="flex justify-between items-center border-b border-white/20 pb-3">
                                        <span className="text-gray-300">Today's Sales</span>
                                        <span className="text-white font-bold text-xl" id="todaySales">TZS 0</span>
                                    </div>
                                    <div className="flex justify-between items-center border-b border-white/20 pb-3">
                                        <span className="text-gray-300">Transactions</span>
                                        <span className="text-white font-bold text-xl" id="todayTransactions">0</span>
                                    </div>
                                    <div className="flex justify-between items-center border-b border-white/20 pb-3">
                                        <span className="text-gray-300">Active Products</span>
                                        <span className="text-white font-bold text-xl" id="productCount">0</span>
                                    </div>
                                    <div className="flex justify-between items-center">
                                        <span className="text-gray-300">Total Customers</span>
                                        <span className="text-white font-bold text-xl" id="customerCount">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>
        </section>
    );
};

export default Hero;