@extends('layouts.app')

@section('title', 'Privacy Policy · cartzy')

@section('content')
<div class="min-h-screen bg-[#FDFBFD] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-sm border border-[#ECE7F2] p-8 sm:p-12">
        <div class="border-b border-[#ECE7F2] pb-6 mb-8">
            <span class="inline-block px-3 py-1 text-xs font-bold tracking-wider text-[#865561] bg-[#F7F2F4] rounded-full uppercase mb-3">Legal &amp; Privacy</span>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">Privacy Policy</h1>
            <p class="mt-2 text-sm text-gray-500">Effective Date: November 14, 2026 · Last Updated: November 14, 2026</p>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 space-y-6 leading-relaxed">
            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">1. Introduction</h2>
                <p>Welcome to <strong>cartzy</strong> ("we", "our", or "us"). We operate the e-commerce marketplace platform available at <a href="https://cartzyy.shop" class="text-[#865561] font-semibold hover:underline">https://cartzyy.shop</a> and our corresponding mobile application. We respect your privacy and are committed to protecting your personal data in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173 of the Philippines) and international data protection standards.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">2. Information We Collect</h2>
                <p>We collect information that you provide directly to us when creating an account, browsing products, making purchases, or signing in:</p>
                <ul class="list-disc pl-5 space-y-1.5 mt-2">
                    <li><strong>Account Information:</strong> Name, email address, password, phone number, and delivery address.</li>
                    <li><strong>Google Sign-In Data:</strong> When you choose to sign in using Google, we access basic public profile information authorized by you: your <em>name</em>, <em>email address</em>, <em>Google User ID</em>, and <em>profile photo URL</em>. We do not access your contacts, private files, or search history.</li>
                    <li><strong>Transaction &amp; Order Data:</strong> Details of orders, products purchased, payment confirmation codes (e.g., GCash reference numbers), and delivery tracking updates.</li>
                    <li><strong>Device &amp; Usage Information:</strong> Technical data such as device type, IP address, browser type, and operating system when interacting with our platform.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">3. How We Use Your Information</h2>
                <p>We use your personal data strictly for legitimate business and fulfillment purposes:</p>
                <ul class="list-disc pl-5 space-y-1.5 mt-2">
                    <li>To authenticate your identity and maintain your account securely.</li>
                    <li>To process, confirm, and deliver marketplace orders between buyers, sellers, and logistics riders.</li>
                    <li>To provide real-time order tracking and shipment notifications.</li>
                    <li>To respond to customer service inquiries and support requests.</li>
                    <li>To protect against fraud, unauthorized transactions, and maintain platform security.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">4. Google User Data Policy</h2>
                <p>Our use of information received from Google APIs adheres to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener" class="text-[#865561] font-semibold hover:underline">Google API Services User Data Policy</a>, including the Limited Use requirements:</p>
                <ul class="list-disc pl-5 space-y-1.5 mt-2">
                    <li>We only request scopes necessary for authentication (<code>email</code>, <code>profile</code>, <code>openid</code>).</li>
                    <li>We do not transfer or sell Google user data to third parties, data brokers, or advertising networks.</li>
                    <li>We do not use Google user data for serving personalized advertisements.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">5. Data Sharing and Disclosure</h2>
                <p>We do not sell, rent, or trade your personal information. We only share information with:</p>
                <ul class="list-disc pl-5 space-y-1.5 mt-2">
                    <li><strong>Logistics and Courier Partners:</strong> Delivery details (name, delivery address, contact number) necessary to fulfill parcel pickup and delivery.</li>
                    <li><strong>Payment Processors:</strong> Necessary transaction references for verifying GCash and payment transactions.</li>
                    <li><strong>Legal &amp; Regulatory Authorities:</strong> When required by Philippine law or legal subpoena.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">6. Data Retention and Security</h2>
                <p>We implement industry-standard encryption, SSL/HTTPS secure transmission, and strict access controls to safeguard your personal data. We retain personal data only as long as necessary to fulfill marketplace transactions and comply with statutory obligations.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">7. Your Rights and Choices</h2>
                <p>Under applicable data privacy laws, you have the right to access, update, correct, or request the deletion of your personal account data at any time through your Profile Settings or by contacting our data protection representative.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-2">8. Contact Us</h2>
                <p>If you have any questions, concerns, or requests regarding this Privacy Policy, please contact us at:</p>
                <div class="mt-3 p-4 bg-[#F9F7FA] rounded-2xl border border-[#EEE7F2] text-sm">
                    <p class="font-bold text-gray-900">cartzy Philippines</p>
                    <p class="text-gray-600">Email: <a href="mailto:jessica.p.bajamundi@gmail.com" class="text-[#865561] font-semibold hover:underline">jessica.p.bajamundi@gmail.com</a></p>
                    <p class="text-gray-600">Website: <a href="https://cartzyy.shop" class="text-[#865561] font-semibold hover:underline">https://cartzyy.shop</a></p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

