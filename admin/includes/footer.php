        </main>

        <footer class="admin-footer">
            <p>© <?= date('Y') ?> <?= e(APP_NAME) ?> Admin — <?= e(APP_VERSION) ?> <?= PAYMENT_MODE === 'demo' ? '· <span class="demo-flag">Demo payment mode</span>' : '' ?></p>
        </footer>
    </div>
</div>

<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
