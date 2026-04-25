import React, { useState, useEffect } from 'react';
import { motion, useScroll, useTransform } from 'framer-motion';
import { Helmet } from 'react-helmet-async';
import 'bootstrap/dist/css/bootstrap.min.css';
import './App.css';

// Components
import Navbar from './components/Navbar';
import Hero from './components/Hero';
import Features from './components/Features';
import Stats from './components/Stats';
import Products from './components/Products';
import SalesChart from './components/SalesChart';
import AIChatbot from './components/AIChatbot';
import Pricing from './components/Pricing';
import Testimonials from './components/Testimonials';
import Footer from './components/Footer';

function App() {
    const [darkMode, setDarkMode] = useState(false);
    const { scrollYProgress } = useScroll();
    const opacity = useTransform(scrollYProgress, [0, 0.5], [1, 0.8]);
    
    useEffect(() => {
        // Apply dark mode class
        if (darkMode) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }, [darkMode]);
    
    return (
        <>
            <Helmet>
                <title>SmartPOS - Ultimate Point of Sale System for Tanzania</title>
                <meta name="description" content="SmartPOS - Complete business management solution for Tanzanian businesses" />
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
            </Helmet>
            
            <motion.div style={{ opacity }} className="app-container">
                <Navbar darkMode={darkMode} setDarkMode={setDarkMode} />
                <Hero />
                <Features />
                <Stats />
                <Products />
                <SalesChart />
                <Pricing />
                <Testimonials />
                <Footer />
                <AIChatbot />
            </motion.div>
        </>
    );
}

export default App;