<?php
include_once 'includes/header.php';
?>

  <!-- Hero Section -->
  <section class="container mx-auto px-4 py-20 text-center">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 bg-gradient-to-r from-purple-600 via-blue-600 to-teal-600 bg-clip-text text-transparent leading-tight">
        Your Mental Wellness Companion
      </h2>
      <p class="text-xl text-gray-600 mb-4 max-w-3xl mx-auto leading-relaxed">
        A safe, private, and intuitive platform designed to support students through academic pressure, stress, and anxiety.
        Your journey to emotional well-being starts here.
      </p>
      <p class="text-lg text-emerald-600 font-semibold mb-8 flex items-center justify-center gap-2">
        <span class="animate-bounce">👨‍👩‍👧‍👦</span> Parents can now monitor and support their child's wellness journey!
      </p>
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="signup.php" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl text-lg font-bold hover:scale-105 transition-transform shadow-xl shadow-blue-200 flex items-center justify-center gap-2">
          Get Started <i data-lucide="arrow-right" class="size-5"></i>
        </a>
        <a href="parent-signup.php" class="w-full sm:w-auto px-8 py-4 bg-white border-2 border-emerald-100 text-emerald-600 rounded-xl text-lg font-bold hover:bg-emerald-50 transition-all flex items-center justify-center gap-2">
          Parent Access
        </a>
      </div>
    </div>
  </section>

  <!-- Features Grid -->
  <section id="features" class="container mx-auto px-4 py-16">
    <h3 class="text-3xl font-bold text-center mb-12 text-gray-800 relative inline-block w-full">
      Why Choose MindCare?

    </h3>
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
      <div class="p-8 bg-white rounded-3xl border-2 border-purple-100 hover:border-purple-300 transition-all hover:shadow-2xl group">
        <div class="size-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
          <i data-lucide="shield" class="size-8 text-purple-600"></i>
        </div>
        <h4 class="font-bold text-xl mb-3 text-gray-800">Safe & Private</h4>
        <p class="text-gray-600 text-sm leading-relaxed">
          Your data is encrypted and secure. We prioritize your privacy above all levels of interaction.
        </p>
      </div>

      <div class="p-8 bg-white rounded-3xl border-2 border-blue-100 hover:border-blue-300 transition-all hover:shadow-2xl group">
        <div class="size-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
          <i data-lucide="brain" class="size-8 text-blue-600"></i>
        </div>
        <h4 class="font-bold text-xl mb-3 text-gray-800">AI-Powered Analysis</h4>
        <p class="text-gray-600 text-sm leading-relaxed">
          Daily mood assessments with personalized exercise recommendations using advanced AI.
        </p>
      </div>

      <div class="p-8 bg-white rounded-3xl border-2 border-teal-100 hover:border-teal-300 transition-all hover:shadow-2xl group">
        <div class="size-16 bg-teal-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
          <i data-lucide="heart" class="size-8 text-teal-600"></i>
        </div>
        <h4 class="font-bold text-xl mb-3 text-gray-800">Guided Exercises</h4>
        <p class="text-gray-600 text-sm leading-relaxed">
          Meditation, breathing, and relaxation exercises tailored to your current emotional state.
        </p>
      </div>

      <div class="p-8 bg-white rounded-3xl border-2 border-pink-100 hover:border-pink-300 transition-all hover:shadow-2xl group">
        <div class="size-16 bg-pink-100 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
          <i data-lucide="users" class="size-8 text-pink-600"></i>
        </div>
        <h4 class="font-bold text-xl mb-3 text-gray-800">Crisis Support</h4>
        <p class="text-gray-600 text-sm leading-relaxed">
          Emergency contact alerts and counseling resources when you need them most in difficult times.
        </p>
      </div>
    </div>
  </section>

  <!-- Purpose Section -->
  <section id="about" class="container mx-auto px-4 py-16">
    <div class="p-12 bg-gradient-to-r from-purple-600 to-blue-600 rounded-[3rem] shadow-2xl relative overflow-hidden group">
      <div class="absolute top-0 right-0 size-64 bg-white/10 rounded-full blur-3xl -mr-32 -mt-32 transition-all group-hover:scale-110"></div>
      <div class="relative z-10">
        <h3 class="text-3xl md:text-4xl font-bold mb-10 text-center text-white">Our Purpose</h3>
        <div class="grid md:grid-cols-3 gap-12 text-center md:text-left">
          <div class="space-y-4">
            <div class="size-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
              <i data-lucide="sparkles" class="size-6 text-white text-xl">✨</i>
            </div>
            <h4 class="font-bold text-2xl text-purple-100">For Students</h4>
            <p class="text-purple-50/80 leading-relaxed">
              Navigate academic pressures with tools designed specifically for student life.
              Build resilience and maintain balance.
            </p>
          </div>
          <div class="space-y-4">
            <div class="size-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
              <i data-lucide="activity" class="size-6 text-white text-xl">📈</i>
            </div>
            <h4 class="font-bold text-2xl text-blue-100">Evidence-Based</h4>
            <p class="text-blue-50/80 leading-relaxed">
              Our exercises and assessments are based on proven psychological techniques
              and CBT principles.
            </p>
          </div>
          <div class="space-y-4">
            <div class="size-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
              <i data-lucide="clock" class="size-6 text-white text-xl">🕒</i>
            </div>
            <h4 class="font-bold text-2xl text-teal-100">24/7 Access</h4>
            <p class="text-teal-50/80 leading-relaxed">
              Get support whenever you need it. Track your progress and access resources
              anytime, anywhere.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- System Guidelines -->
  <section class="container mx-auto px-4 py-16">
    <h3 class="text-3xl font-bold text-center mb-12 text-gray-800">How It Works</h3>
    <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-6">
      <div class="p-6 bg-white rounded-2xl border-2 border-gray-100 flex gap-4 hover:shadow-md transition-shadow">
        <div class="text-3xl">📋</div>
        <div>
          <h4 class="font-bold mb-1 text-gray-800">Daily Check-ins</h4>
          <p class="text-gray-600 text-sm">Complete assessment to get personalized exercise recommendations.</p>
        </div>
      </div>
      <div class="p-6 bg-white rounded-2xl border-2 border-gray-100 flex gap-4 hover:shadow-md transition-shadow">
        <div class="text-3xl">🧘</div>
        <div>
          <h4 class="font-bold mb-1 text-gray-800">Practice Exercises</h4>
          <p class="text-gray-600 text-sm">Follow guided meditations. Consistency is key to results.</p>
        </div>
      </div>
      <div class="p-6 bg-white rounded-2xl border-2 border-gray-100 flex gap-4 hover:shadow-md transition-shadow">
        <div class="text-3xl">📊</div>
        <div>
          <h4 class="font-bold mb-1 text-gray-800">Monitor Progress</h4>
          <p class="text-gray-600 text-sm">Review mood trends and celebrate improvements. Your journey matters.</p>
        </div>
      </div>
      <div class="p-6 bg-white rounded-2xl border-2 border-gray-100 flex gap-4 hover:shadow-md transition-shadow">
        <div class="text-3xl">🚨</div>
        <div>
          <h4 class="font-bold mb-1 text-gray-800">Emergency Support</h4>
          <p class="text-gray-600 text-sm">The system can alert emergency contacts. Add them in settings.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="container mx-auto px-4 py-20 text-center">
    <div class="max-w-2xl mx-auto">
      <h3 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">Ready to Start Your Wellness Journey?</h3>
      <p class="text-lg text-gray-600 mb-8 max-w-lg mx-auto">
        Join thousands of students taking control of their mental health and building a better future.
      </p>
      <a href="signup.php" class="inline-block px-12 py-5 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-2xl text-xl font-bold hover:scale-105 transition-transform shadow-2xl shadow-blue-200">
        Create Free Account
      </a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400 py-12 border-t border-gray-800">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="flex items-center gap-2">
          <i data-lucide="brain" class="size-6 text-purple-500"></i>
          <span class="text-xl font-bold text-white">MindCare</span>
        </div>
        <div class="flex gap-8 text-sm">
          <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
          <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
          <a href="#" class="hover:text-white transition-colors">Contact Us</a>
        </div>
      </div>
      <div class="mt-12 text-center text-sm">
        <p>© 2026 MindCare - Your Mental Health Companion</p>
        <p class="mt-2 text-gray-600">This is a supportive tool, not a replacement for professional mental health care.</p>
      </div>
    </div>
  </footer>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>